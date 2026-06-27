<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // Tangani GET request (Riwayat & Cetak Struk)
    public function handleGet(Request $request)
    {
        $action = $request->query('action');
        
        if ($action === 'history') {
            $limit = $request->query('limit', 20);
            $history = DB::table('transaksi')->orderBy('id', 'desc')->limit($limit)->get();
            return response()->json(['success' => true, 'data' => $history]);
        } 
        
        if ($action === 'receipt') {
            $id = $request->query('id');
            $transaksi = DB::table('transaksi')->where('id', $id)->first();
            $items = DB::table('detail_transaksi')->where('transaksi_id', $id)->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'transaksi' => $transaksi,
                    'items'     => $items,
                    'toko'      => [
                        'nama'    => 'Angkringan Songolimo',
                        'alamat'  => 'Jl. Songolimo',
                        'telepon' => '0812345678',
                        'pesan'   => 'Terima kasih!'
                    ]
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Action invalid'], 400);
    }

    // Tangani POST request (Hitung & Checkout Pembayaran)
    public function handlePost(Request $request)
    {
        $action = $request->query('action');
        
        if ($action === 'calculate') {
            return response()->json([
                'success' => true, 
                'data' => $this->hitungTotal($request->all())
            ]);
        } 
        
        if ($action === 'checkout') {
            return $this->prosesCheckout($request);
        }

        return response()->json(['success' => false, 'message' => 'Action invalid'], 400);
    }

    private function prosesCheckout(Request $request)
    {
        $data = $request->all();
        if (empty($data['items'])) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong'], 422);
        }

        $uangDiterima = (float) ($data['uang_diterima'] ?? 0);
        $calc = $this->hitungTotal($data);

        if ($uangDiterima < $calc['grand_total']) {
            return response()->json(['success' => false, 'message' => 'Uang tidak cukup'], 422);
        }

        $uangKembali = $uangDiterima - $calc['grand_total'];
        $noStruk = 'INV-' . time() . '-' . rand(10, 99);

        // Mulai transaksi aman Laravel (kebal HY093)
        DB::beginTransaction();
        try {
            // 1. Simpan ke tabel transaksi
            $transaksiId = DB::table('transaksi')->insertGetId([
                'no_struk'       => $noStruk,
                'subtotal'       => $calc['subtotal'],
                'pajak_persen'   => $calc['pajak_persen'],
                'pajak_nominal'  => $calc['pajak_nominal'],
                'diskon_persen'  => $calc['diskon_persen'],
                'diskon_nominal' => $calc['diskon_nominal'],
                'grand_total'    => $calc['grand_total'],
                'uang_diterima'  => $uangDiterima,
                'uang_kembali'   => $uangKembali,
                'created_at'     => now(),
            ]);

            // 2. Simpan detail dan potong stok
            foreach ($calc['items'] as $item) {
                DB::table('detail_transaksi')->insert([
                    'transaksi_id' => $transaksiId,
                    'menu_id'      => $item['menu_id'],
                    'nama_menu'    => $item['nama'],
                    'harga_satuan' => $item['harga'],
                    'kuantitas'    => $item['kuantitas'],
                    'total_harga'  => $item['total'],
                ]);

                DB::table('menu')->where('id', $item['menu_id'])->decrement('stok', $item['kuantitas']);
            }

            DB::commit();

            // Ambil struk kembalian
            $transaksi = DB::table('transaksi')->where('id', $transaksiId)->first();
            $items = DB::table('detail_transaksi')->where('transaksi_id', $transaksiId)->get();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'data' => [
                    'transaksi' => $transaksi,
                    'items'     => $items,
                    'toko'      => [
                        'nama'    => 'Angkringan Songolimo',
                        'alamat'  => 'Jl. Songolimo',
                        'telepon' => '0812345678',
                        'pesan'   => 'Terima kasih!'
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    private function hitungTotal(array $data)
    {
        $items = $data['items'] ?? [];
        $subtotal = 0;
        $detailedItems = [];

        foreach ($items as $item) {
            $menu = DB::table('menu')->where('id', $item['menu_id'])->first();
            if (!$menu) continue;

            $qty = (int) $item['kuantitas'];
            $totalItem = $menu->harga * $qty;
            $subtotal += $totalItem;

            $detailedItems[] = [
                'menu_id'   => $menu->id,
                'nama'      => $menu->nama,
                'kategori'  => $menu->kategori,
                'harga'     => (float) $menu->harga,
                'kuantitas' => $qty,
                'total'     => $totalItem,
            ];
        }

        $pajakPersen = (float) ($data['pajak_persen'] ?? 10);
        $diskonPersen = (float) ($data['diskon_persen'] ?? 0);
        $pajakNominal = round($subtotal * ($pajakPersen / 100), 2);
        $diskonNominal = round($subtotal * ($diskonPersen / 100), 2);
        $grandTotal = max(0, $subtotal + $pajakNominal - $diskonNominal);

        return [
            'items'          => $detailedItems,
            'subtotal'       => $subtotal,
            'pajak_persen'   => $pajakPersen,
            'pajak_nominal'  => $pajakNominal,
            'diskon_persen'  => $diskonPersen,
            'diskon_nominal' => $diskonNominal,
            'grand_total'    => $grandTotal
        ];
    }
}
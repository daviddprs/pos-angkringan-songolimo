<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Halaman utama — tampilkan menu & keranjang
     */
    public function index(Request $request)
    {
        $kategori = $request->get('kategori', 'semua');

        $query = Menu::tersedia()->orderBy('nama');

        if ($kategori !== 'semua') {
            $query->kategori($kategori);
        }

        $menus      = $query->get()->groupBy('kategori');
        $semua      = Menu::tersedia()->orderBy('nama')->get();
        $kategoris  = Menu::tersedia()->distinct()->pluck('kategori')->sort()->values();

        return view('menu.index', compact('menus', 'semua', 'kategoris', 'kategori'));
    }

    /**
     * Simpan order ke database (POST dari form keranjang)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan'     => 'nullable|string|max:100',
            'meja'               => 'nullable|string|max:20',
            'metode_bayar'       => 'required|in:tunai,transfer,qris',
            'bayar'              => 'required_if:metode_bayar,tunai|nullable|numeric|min:0',
            'catatan'            => 'nullable|string|max:500',
            'items'              => 'required|array|min:1',
            'items.*.menu_id'    => 'required|exists:menus,id',
            'items.*.qty'        => 'required|integer|min:1|max:99',
            'items.*.catatan'    => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            // Hitung total dari DB (bukan dari client — hindari manipulasi harga)
            $totalHarga = 0;
            $itemsData  = [];

            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);

                if (! $menu->tersedia) {
                    return back()->withErrors(['items' => "Menu '{$menu->nama}' sedang tidak tersedia."])->withInput();
                }

                $subtotal    = $menu->harga * $item['qty'];
                $totalHarga += $subtotal;

                $itemsData[] = [
                    'menu_id'      => $menu->id,
                    'nama_menu'    => $menu->nama,
                    'harga_satuan' => $menu->harga,
                    'qty'          => $item['qty'],
                    'subtotal'     => $subtotal,
                    'catatan'      => $item['catatan'] ?? null,
                ];
            }

            $bayar     = $request->metode_bayar === 'tunai' ? (float) $request->bayar : $totalHarga;
            $kembalian = max(0, $bayar - $totalHarga);

            if ($request->metode_bayar === 'tunai' && $bayar < $totalHarga) {
                return back()->withErrors(['bayar' => 'Nominal bayar kurang dari total harga.'])->withInput();
            }

            $order = Order::create([
                'kode_order'     => Order::generateKode(),
                'nama_pelanggan' => $request->nama_pelanggan,
                'meja'           => $request->meja,
                'status'         => 'pending',
                'metode_bayar'   => $request->metode_bayar,
                'total_harga'    => $totalHarga,
                'bayar'          => $bayar,
                'kembalian'      => $kembalian,
                'catatan'        => $request->catatan,
                'bayar_at'       => now(),
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            return redirect()->route('orders.sukses', $order)
                ->with('success', "Order {$order->kode_order} berhasil disimpan!");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors(['general' => 'Terjadi kesalahan, coba lagi.'])->withInput();
        }
    }

    /**
     * Halaman sukses / struk order
     */
    public function sukses(Order $order)
    {
        $order->load('items.menu');

        return view('orders.sukses', compact('order'));
    }

    /**
     * Daftar riwayat order (kasir)
     */
    public function riwayat(Request $request)
    {
        $orders = Order::with('items')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('orders.riwayat', compact('orders'));
    }

    /**
     * Update status order
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai,dibatalkan',
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', "Status order diubah ke '{$request->status}'.");
    }
}

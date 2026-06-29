@extends('layouts.app')
@section('title', 'Pesanan Berhasil — ' . $order->kode_order)

@section('content')
<div class="max-w-lg mx-auto">

    {{-- Card Struk --}}
    <div class="bg-white rounded-2xl shadow-md border border-amber-100 overflow-hidden" id="struk">

        {{-- Header --}}
        <div class="bg-amber-700 text-white text-center py-5 px-4">
            <div class="text-4xl mb-1">🍢</div>
            <h1 class="font-bold text-lg">Angkringan Songolimo</h1>
            <p class="text-amber-200 text-xs mt-0.5">Struk Pemesanan</p>
        </div>

        {{-- Info Order --}}
        <div class="px-5 py-4 border-b border-dashed border-amber-200">
            <div class="grid grid-cols-2 gap-y-1 text-sm">
                <span class="text-gray-500">Kode Order</span>
                <span class="font-bold text-amber-800 text-right">{{ $order->kode_order }}</span>

                <span class="text-gray-500">Waktu</span>
                <span class="text-right">{{ $order->created_at->format('d/m/Y H:i') }}</span>

                @if ($order->nama_pelanggan)
                <span class="text-gray-500">Pelanggan</span>
                <span class="text-right">{{ $order->nama_pelanggan }}</span>
                @endif

                @if ($order->meja)
                <span class="text-gray-500">Meja</span>
                <span class="text-right font-semibold">#{{ $order->meja }}</span>
                @endif

                <span class="text-gray-500">Metode Bayar</span>
                <span class="text-right capitalize">
                    {{ ['tunai' => '💵 Tunai', 'transfer' => '🏦 Transfer', 'qris' => '📱 QRIS'][$order->metode_bayar] }}
                </span>

                <span class="text-gray-500">Status</span>
                <span class="text-right">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status_badge }} capitalize">
                        {{ $order->status }}
                    </span>
                </span>
            </div>
        </div>

        {{-- Daftar Item --}}
        <div class="px-5 py-3 border-b border-dashed border-amber-200">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 border-b border-amber-100">
                        <th class="text-left pb-1.5">Menu</th>
                        <th class="text-center pb-1.5">Qty</th>
                        <th class="text-right pb-1.5">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-50">
                    @foreach ($order->items as $item)
                    <tr>
                        <td class="py-1.5">
                            <p class="font-medium text-gray-800">{{ $item->nama_menu }}</p>
                            <p class="text-xs text-gray-400">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}/pcs</p>
                            @if ($item->catatan)
                            <p class="text-xs text-amber-600 italic">{{ $item->catatan }}</p>
                            @endif
                        </td>
                        <td class="text-center py-1.5 font-semibold">{{ $item->qty }}</td>
                        <td class="text-right py-1.5 font-semibold text-amber-800">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Ringkasan Bayar --}}
        <div class="px-5 py-4">
            <div class="space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Harga</span>
                    <span class="font-semibold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Bayar</span>
                    <span>Rp {{ number_format($order->bayar, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-base font-bold text-amber-800 border-t border-amber-200 pt-2 mt-2">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($order->kembalian, 0, ',', '.') }}</span>
                </div>
            </div>

            @if ($order->catatan)
            <div class="mt-3 bg-amber-50 rounded-lg p-2 text-xs text-amber-800">
                📝 <em>{{ $order->catatan }}</em>
            </div>
            @endif
        </div>

        {{-- Footer Struk --}}
        <div class="bg-amber-50 text-center text-xs text-amber-600 py-3 border-t border-amber-100">
            Terima kasih sudah mampir! 🙏
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="flex gap-3 mt-5">
        <a href="{{ route('menu.index') }}"
           class="flex-1 bg-amber-700 hover:bg-amber-800 text-white text-center
                  py-2.5 rounded-xl font-semibold text-sm transition">
            ➕ Pesan Lagi
        </a>
        <a href="{{ route('orders.riwayat') }}"
           class="flex-1 bg-white border border-amber-300 hover:bg-amber-50 text-amber-800
                  text-center py-2.5 rounded-xl font-semibold text-sm transition">
            📋 Lihat Riwayat
        </a>
        <button onclick="window.print()"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                       py-2.5 rounded-xl font-semibold text-sm transition">
            🖨 Print
        </button>
    </div>
</div>

<style>
@media print {
    header, footer, .flex.gap-3 { display: none !important; }
    body { background: white; }
    #struk { box-shadow: none; border: none; max-width: 100%; }
}
</style>
@endsection

@extends('layouts.app')
@section('title', 'Riwayat Order — Kasir')

@section('content')
<div class="flex items-center justify-between mb-5">
    <h2 class="text-xl font-bold text-amber-900">📋 Riwayat Order</h2>
    <a href="{{ route('menu.index') }}"
       class="bg-amber-700 hover:bg-amber-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
        ➕ Order Baru
    </a>
</div>

@if ($orders->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-3">📭</div>
        <p>Belum ada order masuk.</p>
    </div>
@else
<div class="space-y-3">
    @foreach ($orders as $order)
    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-amber-50">
            {{-- Info utama --}}
            <div class="flex items-center gap-3">
                <div>
                    <p class="font-bold text-amber-800 text-sm">{{ $order->kode_order }}</p>
                    <p class="text-xs text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @if ($order->meja)
                <span class="bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                    Meja #{{ $order->meja }}
                </span>
                @endif
                @if ($order->nama_pelanggan)
                <span class="text-xs text-gray-600">{{ $order->nama_pelanggan }}</span>
                @endif
            </div>

            {{-- Badge status + total --}}
            <div class="text-right">
                <p class="font-bold text-amber-800 text-sm">{{ $order->total_format }}</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $order->status_badge }} capitalize mt-0.5">
                    {{ $order->status }}
                </span>
            </div>
        </div>

        {{-- Item list ringkas --}}
        <div class="px-4 py-2 text-xs text-gray-600 flex flex-wrap gap-2">
            @foreach ($order->items as $item)
            <span class="bg-amber-50 rounded-full px-2 py-0.5">
                {{ $item->qty }}× {{ $item->nama_menu }}
            </span>
            @endforeach
        </div>

        {{-- Aksi ubah status --}}
        <div class="px-4 py-2 bg-amber-50 border-t border-amber-100 flex items-center gap-2 flex-wrap">
            <span class="text-xs text-gray-500 mr-1">Ubah status:</span>
            @foreach (['pending' => '⏳', 'diproses' => '🔥', 'selesai' => '✅', 'dibatalkan' => '❌'] as $st => $icon)
            <form method="POST" action="{{ route('orders.status', $order) }}" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $st }}">
                <button type="submit"
                        class="text-xs px-2 py-1 rounded-lg transition
                               {{ $order->status === $st
                                  ? 'bg-amber-700 text-white font-semibold'
                                  : 'bg-white border border-gray-200 text-gray-600 hover:bg-amber-100' }}">
                    {{ $icon }} {{ ucfirst($st) }}
                </button>
            </form>
            @endforeach

            <a href="{{ route('orders.sukses', $order) }}"
               class="ml-auto text-xs text-amber-700 hover:underline">
                🖨 Lihat Struk
            </a>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
<div class="mt-5">
    {{ $orders->links() }}
</div>
@endif
@endsection

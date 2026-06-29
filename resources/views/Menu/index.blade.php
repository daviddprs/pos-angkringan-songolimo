@extends('layouts.app')
@section('title', 'Pilih Menu — Angkringan Songolimo')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="keranjang()">

    {{-- ===================== KIRI: Daftar Menu ===================== --}}
    <div class="lg:col-span-2">

        {{-- Filter Kategori --}}
        <div class="flex gap-2 flex-wrap mb-4">
            @foreach ([['semua','🍽 Semua'], ['makanan','🍱 Makanan'], ['minuman','🥤 Minuman'], ['snack','🍞 Snack']] as [$val, $label])
            <button @click="filterKategori = '{{ $val }}'"
                    :class="filterKategori === '{{ $val }}'
                        ? 'bg-amber-700 text-white'
                        : 'bg-white text-amber-800 border border-amber-300 hover:bg-amber-100'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition shadow-sm">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Error validasi --}}
        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4 text-sm text-red-700">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- Grid Menu per Kategori --}}
        @foreach (['makanan' => '🍱 Makanan', 'minuman' => '🥤 Minuman', 'snack' => '🍞 Snack'] as $kat => $label)
        @if (isset($menus[$kat]))
        <div x-show="filterKategori === 'semua' || filterKategori === '{{ $kat }}'" x-cloak>
            <h2 class="text-amber-900 font-bold text-base mb-2 mt-2 flex items-center gap-2">
                {{ $label }}
                <span class="text-xs font-normal text-amber-600">({{ $menus[$kat]->count() }} item)</span>
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
                @foreach ($menus[$kat] as $menu)
                <button type="button"
                        @click="tambah({{ $menu->id }}, '{{ addslashes($menu->nama) }}', {{ $menu->harga }})"
                        class="bg-white rounded-2xl shadow-sm border border-amber-100 p-3
                               hover:shadow-md hover:border-amber-400 active:scale-95
                               transition-all text-left group relative">
                    {{-- Ikon Kategori --}}
                    <div class="text-3xl mb-1.5 text-center">
                        {{ $kat === 'makanan' ? '🍱' : ($kat === 'minuman' ? '🥤' : '🍞') }}
                    </div>
                    <p class="font-semibold text-amber-900 text-sm leading-tight">{{ $menu->nama }}</p>
                    @if ($menu->deskripsi)
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $menu->deskripsi }}</p>
                    @endif
                    <p class="text-amber-700 font-bold text-sm mt-1">{{ $menu->harga_format }}</p>

                    {{-- Badge jumlah di keranjang --}}
                    <template x-if="jumlahItem({{ $menu->id }}) > 0">
                        <span class="absolute top-2 right-2 bg-amber-600 text-white text-xs
                                     font-bold rounded-full w-5 h-5 flex items-center justify-center shadow"
                              x-text="jumlahItem({{ $menu->id }})"></span>
                    </template>

                    {{-- Overlay "Tambah" --}}
                    <span class="absolute inset-0 rounded-2xl flex items-center justify-center
                                 bg-amber-600/0 group-hover:bg-amber-600/5 transition pointer-events-none">
                    </span>
                </button>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>

    {{-- ===================== KANAN: Keranjang & Form ===================== --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-amber-100 sticky top-20 overflow-hidden">

            {{-- Header Keranjang --}}
            <div class="bg-amber-700 text-white px-4 py-3 flex items-center justify-between">
                <span class="font-bold flex items-center gap-1.5">🛒 Pesanan</span>
                <span class="text-xs bg-amber-600 rounded-full px-2 py-0.5"
                      x-text="keranjang.length + ' item'"></span>
            </div>

            {{-- Isi Keranjang --}}
            <div class="px-4 py-3 max-h-64 overflow-y-auto divide-y divide-amber-50" id="keranjang-list">

                <template x-if="keranjang.length === 0">
                    <p class="text-gray-400 text-sm text-center py-6">Belum ada pesanan</p>
                </template>

                <template x-for="(item, i) in keranjang" :key="item.id">
                    <div class="py-2 flex items-center gap-2">
                        {{-- Qty control --}}
                        <div class="flex items-center gap-1 shrink-0">
                            <button @click="kurang(i)"
                                    class="w-6 h-6 rounded-full bg-amber-100 hover:bg-amber-200
                                           text-amber-800 font-bold text-sm flex items-center justify-center">
                                −
                            </button>
                            <span class="w-5 text-center text-sm font-semibold" x-text="item.qty"></span>
                            <button @click="tambahLagi(i)"
                                    class="w-6 h-6 rounded-full bg-amber-100 hover:bg-amber-200
                                           text-amber-800 font-bold text-sm flex items-center justify-center">
                                +
                            </button>
                        </div>
                        {{-- Nama & harga --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" x-text="item.nama"></p>
                            <p class="text-xs text-amber-600" x-text="formatRp(item.harga * item.qty)"></p>
                        </div>
                        {{-- Hapus --}}
                        <button @click="hapus(i)" class="text-red-400 hover:text-red-600 text-xs shrink-0">✕</button>
                    </div>
                </template>
            </div>

            {{-- Total --}}
            <div class="px-4 py-2 bg-amber-50 border-t border-amber-100 flex justify-between items-center">
                <span class="text-sm font-semibold text-gray-700">Total</span>
                <span class="text-amber-800 font-bold" x-text="formatRp(total)"></span>
            </div>

            {{-- Form Order --}}
            <form method="POST" action="{{ route('orders.store') }}" @submit.prevent="submitOrder($el)">
                @csrf
                <div class="px-4 py-3 space-y-3">

                    {{-- Hidden items array --}}
                    <div id="hidden-items"></div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs font-medium text-gray-600 block mb-1">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan"
                                   value="{{ old('nama_pelanggan') }}"
                                   placeholder="Opsional"
                                   class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-600 block mb-1">Nomor Meja</label>
                            <input type="text" name="meja"
                                   value="{{ old('meja') }}"
                                   placeholder="Misal: 3"
                                   class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Metode Bayar</label>
                        <select name="metode_bayar" x-model="metodeBayar"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <option value="tunai">💵 Tunai</option>
                            <option value="transfer">🏦 Transfer</option>
                            <option value="qris">📱 QRIS</option>
                        </select>
                    </div>

                    {{-- Nominal bayar (hanya tunai) --}}
                    <div x-show="metodeBayar === 'tunai'" x-cloak>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Nominal Bayar (Rp)</label>
                        <input type="number" name="bayar" x-model="nominalBayar"
                               min="0" step="500"
                               :placeholder="total"
                               value="{{ old('bayar') }}"
                               class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-amber-400">
                        {{-- Kembalian preview --}}
                        <template x-if="nominalBayar && nominalBayar >= total">
                            <p class="text-xs text-green-700 mt-1 font-medium">
                                Kembalian: <span x-text="formatRp(nominalBayar - total)"></span>
                            </p>
                        </template>
                        <template x-if="nominalBayar && nominalBayar < total">
                            <p class="text-xs text-red-500 mt-1">Bayar kurang!</p>
                        </template>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Catatan</label>
                        <textarea name="catatan" rows="2"
                                  placeholder="Misal: tidak pedas, tambah kecap..."
                                  class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm
                                         focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
                        >{{ old('catatan') }}</textarea>
                    </div>

                    <button type="submit"
                            :disabled="keranjang.length === 0"
                            :class="keranjang.length === 0
                                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                : 'bg-amber-700 hover:bg-amber-800 text-white cursor-pointer'"
                            class="w-full py-2.5 rounded-xl font-bold text-sm transition shadow-sm">
                        ✅ Buat Pesanan
                    </button>

                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Alpine.js CDN --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function keranjang() {
    return {
        keranjang: [],
        filterKategori: 'semua',
        metodeBayar: 'tunai',
        nominalBayar: null,

        get total() {
            return this.keranjang.reduce((s, i) => s + i.harga * i.qty, 0);
        },

        tambah(id, nama, harga) {
            const idx = this.keranjang.findIndex(i => i.id === id);
            if (idx >= 0) {
                this.keranjang[idx].qty++;
            } else {
                this.keranjang.push({ id, nama, harga, qty: 1 });
            }
        },

        tambahLagi(i) { this.keranjang[i].qty++; },

        kurang(i) {
            if (this.keranjang[i].qty <= 1) {
                this.hapus(i);
            } else {
                this.keranjang[i].qty--;
            }
        },

        hapus(i) { this.keranjang.splice(i, 1); },

        jumlahItem(id) {
            const f = this.keranjang.find(i => i.id === id);
            return f ? f.qty : 0;
        },

        formatRp(n) {
            return 'Rp ' + Number(n).toLocaleString('id-ID');
        },

        submitOrder(form) {
            if (this.keranjang.length === 0) {
                alert('Pilih menu dulu!');
                return;
            }

            // Inject hidden inputs items[]
            const container = document.getElementById('hidden-items');
            container.innerHTML = '';
            this.keranjang.forEach((item, i) => {
                const addInput = (name, val) => {
                    const el = document.createElement('input');
                    el.type = 'hidden';
                    el.name = name;
                    el.value = val;
                    container.appendChild(el);
                };
                addInput(`items[${i}][menu_id]`, item.id);
                addInput(`items[${i}][qty]`, item.qty);
            });

            form.submit();
        }
    }
}
</script>
@endpush

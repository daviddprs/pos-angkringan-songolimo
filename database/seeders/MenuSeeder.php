<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // Makanan
            ['nama' => 'Nasi Kucing Oseng', 'kategori' => 'makanan', 'harga' => 3000, 'deskripsi' => 'Nasi porsi kecil dengan oseng tempe'],
            ['nama' => 'Nasi Kucing Ayam', 'kategori' => 'makanan', 'harga' => 3500, 'deskripsi' => 'Nasi porsi kecil dengan ayam suwir'],
            ['nama' => 'Nasi Kucing Teri', 'kategori' => 'makanan', 'harga' => 3000, 'deskripsi' => 'Nasi porsi kecil dengan teri kacang'],
            ['nama' => 'Sate Usus', 'kategori' => 'makanan', 'harga' => 2000, 'deskripsi' => 'Sate usus ayam bakar'],
            ['nama' => 'Sate Telur Puyuh', 'kategori' => 'makanan', 'harga' => 2000, 'deskripsi' => 'Sate telur puyuh bumbu kecap'],
            ['nama' => 'Sate Kulit', 'kategori' => 'makanan', 'harga' => 2000, 'deskripsi' => 'Sate kulit ayam crispy'],
            ['nama' => 'Tempe Bacem', 'kategori' => 'makanan', 'harga' => 2000, 'deskripsi' => 'Tempe bacem manis gurih'],
            ['nama' => 'Tahu Bacem', 'kategori' => 'makanan', 'harga' => 2000, 'deskripsi' => 'Tahu bacem empuk'],
            ['nama' => 'Gorengan Tempe', 'kategori' => 'makanan', 'harga' => 1000, 'deskripsi' => 'Tempe goreng tepung renyah'],
            ['nama' => 'Gorengan Bakwan', 'kategori' => 'makanan', 'harga' => 1000, 'deskripsi' => 'Bakwan sayur crispy'],
            // Minuman
            ['nama' => 'Es Teh Manis', 'kategori' => 'minuman', 'harga' => 3000, 'deskripsi' => 'Teh manis dingin segar'],
            ['nama' => 'Teh Panas', 'kategori' => 'minuman', 'harga' => 2000, 'deskripsi' => 'Teh hangat manis'],
            ['nama' => 'Es Jeruk', 'kategori' => 'minuman', 'harga' => 4000, 'deskripsi' => 'Jeruk peras segar dingin'],
            ['nama' => 'Kopi Hitam', 'kategori' => 'minuman', 'harga' => 3000, 'deskripsi' => 'Kopi tubruk robusta'],
            ['nama' => 'Wedang Jahe', 'kategori' => 'minuman', 'harga' => 4000, 'deskripsi' => 'Jahe hangat dengan gula batu'],
            ['nama' => 'Susu Coklat', 'kategori' => 'minuman', 'harga' => 5000, 'deskripsi' => 'Susu coklat panas/dingin'],
            // Snack
            ['nama' => 'Roti Bakar Coklat', 'kategori' => 'snack', 'harga' => 8000, 'deskripsi' => 'Roti bakar isi coklat meses'],
            ['nama' => 'Roti Bakar Keju', 'kategori' => 'snack', 'harga' => 9000, 'deskripsi' => 'Roti bakar isi keju susu'],
            ['nama' => 'Pisang Bakar', 'kategori' => 'snack', 'harga' => 6000, 'deskripsi' => 'Pisang bakar coklat keju'],
            ['nama' => 'Indomie Goreng', 'kategori' => 'snack', 'harga' => 7000, 'deskripsi' => 'Indomie goreng telur'],
        ];

        foreach ($menus as $menu) {
            Menu::create([...$menu, 'tersedia' => true]);
        }
    }
}

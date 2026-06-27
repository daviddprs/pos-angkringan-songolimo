<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    // Arahkan ke tabel asli lu
    protected $table = 'menu';
    
    // Matikan timestamps bawaan Laravel karena DB lu nggak pakai created_at/updated_at di tabel menu
    public $timestamps = false; 
    
    // Izinkan semua kolom diisi
    protected $guarded = [];
}
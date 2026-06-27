<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua data menu dari database
        $menus = Menu::all();
        
        // Kembalikan dalam format JSON yang bisa dibaca app.js lu
        return response()->json([
            'success' => true,
            'data'    => $menus
        ]);
    }
}
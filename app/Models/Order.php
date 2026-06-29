<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'kode_order',
        'nama_pelanggan',
        'meja',
        'status',
        'metode_bayar',
        'total_harga',
        'bayar',
        'kembalian',
        'catatan',
        'bayar_at',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'bayar'       => 'decimal:2',
        'kembalian'   => 'decimal:2',
        'bayar_at'    => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getTotalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_harga, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'bg-yellow-100 text-yellow-800',
            'diproses'   => 'bg-blue-100 text-blue-800',
            'selesai'    => 'bg-green-100 text-green-800',
            'dibatalkan' => 'bg-red-100 text-red-800',
            default      => 'bg-gray-100 text-gray-800',
        };
    }

    /** Generate kode order unik: ORD-YYYYMMDD-XXX */
    public static function generateKode(): string
    {
        $tanggal = now()->format('Ymd');
        $prefix  = "ORD-{$tanggal}-";
        $last    = static::where('kode_order', 'like', "{$prefix}%")
            ->orderByDesc('kode_order')
            ->value('kode_order');

        $urutan = $last ? (int) substr($last, -3) + 1 : 1;

        return $prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT);
    }
}

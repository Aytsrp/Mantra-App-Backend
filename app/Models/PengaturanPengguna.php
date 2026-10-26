<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanPengguna extends Model
{
    protected $table = 'pengaturan_pengguna';

    protected $fillable = ['user_id', 'mode_gelap', 'bahasa', 'notifikasi', 'pengingat'];

    protected function casts(): array
    {
        return [
            'mode_gelap' => 'boolean',
            'notifikasi' => 'boolean',
            'pengingat' => 'boolean',
        ];
    }
}
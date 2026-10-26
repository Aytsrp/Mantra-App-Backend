<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class WajibPajak extends MantraModel
{
    protected $table = 'wajib_pajak';

    protected $hidden = ['nik', 'nib', 'npwp'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function objekPajak(): HasMany
    {
        return $this->hasMany(ObjekPajak::class, 'id_wajib_pajak');
    }
}
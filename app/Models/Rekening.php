<?php

namespace App\Models;

class Rekening extends MantraModel
{
    protected $table = 'rekening';

    protected function casts(): array
    {
        return ['tarif_persen' => 'float'];
    }
}
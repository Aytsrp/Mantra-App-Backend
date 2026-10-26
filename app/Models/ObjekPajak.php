<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObjekPajak extends MantraModel
{
    use SoftDeletes;

    protected $table = 'objek_pajak';

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function wajibPajak(): BelongsTo
    {
        return $this->belongsTo(WajibPajak::class, 'id_wajib_pajak');
    }

    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'id_jenis_pajak');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'id_rekening');
    }

    public function scopeMilik(Builder $q, int $idWajibPajak): Builder
    {
        return $q->where('id_wajib_pajak', $idWajibPajak);
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeFilterJenis(Builder $q, ?string $kode): Builder
    {
        return $kode === null || $kode === ''
            ? $q
            : $q->whereHas('jenisPajak', fn (Builder $j) => $j->where('kode', $kode));
    }

    public function scopeCari(Builder $q, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $q;
        }
        $like = '%'.mb_strtolower(trim($term)).'%';

        return $q->where(function (Builder $w) use ($like) {
            $w->whereRaw('LOWER(nama) LIKE ?', [$like])
                ->orWhereRaw('LOWER(nopd) LIKE ?', [$like])
                ->orWhereRaw('LOWER(alamat) LIKE ?', [$like]);
        });
    }
}
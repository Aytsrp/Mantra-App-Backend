<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembayaran extends MantraModel
{
    protected $table = 'pembayaran';

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'created_at' => 'datetime'];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    public function wajibPajak(): BelongsTo
    {
        return $this->belongsTo(WajibPajak::class, 'id_wajib_pajak');
    }

    public function objekPajak(): BelongsTo
    {
        return $this->belongsTo(ObjekPajak::class, 'id_objek_pajak')->withTrashed();
    }

    public function jenisPajak(): BelongsTo
    {
        return $this->belongsTo(JenisPajak::class, 'id_jenis_pajak');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PembayaranDetail::class, 'id_pembayaran');
    }

    public function scopeMilik(Builder $q, int $idWajibPajak): Builder
    {
        return $q->where('id_wajib_pajak', $idWajibPajak);
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
            $w->whereRaw('LOWER(CAST(tanggal AS TEXT)) LIKE ?', [$like])
                ->orWhereHas('transaksi', fn (Builder $x) => $x
                    ->whereRaw('LOWER(kode_billing) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(nama_jenis_pajak) LIKE ?', [$like]))
                ->orWhereHas('wajibPajak', fn (Builder $x) => $x->whereRaw('LOWER(nama) LIKE ?', [$like]))
                ->orWhereHas('objekPajak', fn (Builder $x) => $x
                    ->whereRaw('LOWER(nama) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(alamat) LIKE ?', [$like]));
        });
    }
}
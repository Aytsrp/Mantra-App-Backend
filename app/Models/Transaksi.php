<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends MantraModel
{
    protected $table = 'transaksi';
    public const TOTAL_TAGIHAN_SQL = 'COALESCE(pajak_yg_harus_dibayar, final_pajak, jumlah_pajak, 0)';

    protected function casts(): array
    {
        return [
            'tgl_pendataan' => 'date',
            'masa_awal' => 'date',
            'masa_akhir' => 'date',
            'tgl_jatuh_tempo' => 'date',
            'tgl_billing' => 'date',
            'status_pembatalan' => 'boolean',
            'status_koreksi' => 'boolean',
            'tarif_persen' => 'float',
            'created_at' => 'datetime',
        ];
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

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_transaksi');
    }

    public function scopeMilik(Builder $q, int $idWajibPajak): Builder
    {
        return $q->where('id_wajib_pajak', $idWajibPajak);
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('status_pembatalan', false);
    }

    public function scopeSptpd(Builder $q): Builder
    {
        $ids = config('mantra.jenis_surat_sptpd');

        return $ids ? $q->whereIn('id_jenis_surat', $ids) : $q;
    }

    public function scopeFilterJenis(Builder $q, ?string $kode): Builder
    {
        return $kode === null || $kode === ''
            ? $q
            : $q->whereHas('jenisPajak', fn(Builder $j) => $j->where('kode', $kode));
    }

    public function scopeCari(Builder $q, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $q;
        }
        $like = '%' . mb_strtolower(trim($term)) . '%';

        return $q->where(function (Builder $w) use ($like) {
            $w->whereRaw('LOWER(kode_billing) LIKE ?', [$like])
                ->orWhereRaw('LOWER(nama_jenis_pajak) LIKE ?', [$like])
                ->orWhereRaw('LOWER(CAST(tgl_pendataan AS TEXT)) LIKE ?', [$like])
                ->orWhereHas('wajibPajak', fn(Builder $x) => $x->whereRaw('LOWER(nama) LIKE ?', [$like]))
                ->orWhereHas('objekPajak', fn(Builder $x) => $x
                    ->whereRaw('LOWER(nama) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(alamat) LIKE ?', [$like]));
        });
    }

    public function scopeTunggakan(Builder $q): Builder
    {
        return $q->whereNotNull('kode_billing')
            ->where('kode_billing', '<>', '')
            ->whereDoesntHave('pembayaran');
    }

    public function getPokokPajakAttribute(): int
    {
        return (int) ($this->final_pajak ?? $this->jumlah_pajak ?? 0);
    }

    public function getTanggalLaporAttribute()
    {
        return $this->tgl_pendataan ?? $this->created_at?->copy()->startOfDay();
    }

    public function getTotalTagihanAttribute(): int
    {
        return (int) ($this->pajak_yg_harus_dibayar ?? $this->final_pajak ?? $this->jumlah_pajak ?? 0);
    }
}
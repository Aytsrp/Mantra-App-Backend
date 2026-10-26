<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class RiwayatResource extends ApiResource
{
    private const BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    public function toArray(Request $request): array
    {
        $t = $this->resource;
        $bayar = $t->pembayaran->sortByDesc('tanggal')->first();
        $jumlah = $t->pembayaran->sum(fn ($p) => $p->detail->sum('jumlah_pembayaran'));

        return [
            'id' => $t->uuid,
            'bulan' => self::BULAN[(int) $t->masa_awal->month],
            'kode_rekening' => $t->kode_rekening,
            'tanggal' => self::tanggal($t->tanggal_lapor),
            'masa_pajak' => (int) $t->masa_awal->month,
            'tahun' => (int) $t->masa_awal->year,
            'jumlah_pajak' => $t->pokok_pajak,
            'tanggal_pembayaran' => self::tanggal($bayar?->tanggal),
            'jumlah_pembayaran' => $bayar ? (int) $jumlah : null,
        ];
    }
}
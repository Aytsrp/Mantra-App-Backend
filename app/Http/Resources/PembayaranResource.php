<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PembayaranResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $p = $this->resource;
        $t = $p->transaksi;

        return [
            'id' => $p->uuid,
            'jenis_pajak' => $t?->nama_jenis_pajak ?? $p->jenisPajak?->nama,
            'nama_wp' => $p->wajibPajak?->nama,
            'nama_objek' => $p->objekPajak?->nama,
            'alamat' => $p->objekPajak?->alamat,
            'masa_pajak' => $t ? (int) $t->masa_awal->month : null,
            'tahun' => $t ? (int) $t->masa_awal->year : null,
            'pokok_pajak' => $t?->pokok_pajak ?? 0,
            'kode_bayar' => $t?->kode_billing,
            'tanggal' => self::tanggal($p->tanggal),
            'jumlah_pembayaran' => (int) ($p->getAttribute('jumlah_dibayar') ?? 0),
            'sspd_url' => null,
        ];
    }
}
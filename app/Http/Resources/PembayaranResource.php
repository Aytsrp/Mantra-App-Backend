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
            'npwpd' => $p->wajibPajak?->npwpd,
            'alamat_wp' => $p->wajibPajak?->alamat,
            'nama_objek' => $p->objekPajak?->nama,
            'nopd' => $p->objekPajak?->nopd,
            'alamat' => $p->objekPajak?->alamat,
            'rekening' => $p->objekPajak?->rekening?->kode ?? '4.1.1.19.03.001.002.00',
            'no_sptpd' => $p->nomor ?? $t?->kode_billing,
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
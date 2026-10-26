<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class LaporanResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $t = $this->resource;

        return [
            'id' => $t->uuid,
            'nop' => $t->objekPajak?->nopd,
            'nama_wp' => $t->wajibPajak?->nama,
            'nama_objek' => $t->objekPajak?->nama,
            'alamat' => $t->objekPajak?->alamat,
            'jenis_pajak' => $t->nama_jenis_pajak,
            'tanggal' => self::tanggal($t->tanggal_lapor),
            'tahun' => (int) $t->masa_awal->year,
            'masa_pajak' => (int) $t->masa_awal->month,
            'dasar_pengenaan' => $t->dasar_pengenaan !== null ? (int) $t->dasar_pengenaan : null,
            'tarif_persen' => $t->tarif_persen,
            'pokok_pajak' => $t->pokok_pajak,
            'jatuh_tempo' => self::tanggal($t->tgl_jatuh_tempo),
            'kode_bayar' => $t->kode_billing,
            'sptpd_url' => null,
            'status' => null,
        ];
    }
}
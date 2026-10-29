<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class TunggakanResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $t = $this->resource;

        return [
            'id' => $t->uuid,
            'tanggal' => self::tanggal($t->tanggal_lapor),
            'jenis_pajak' => $t->nama_jenis_pajak,
            'nama_wp' => $t->wajibPajak?->nama,
            'npwpd' => $t->wajibPajak?->npwpd,
            'alamat_wp' => $t->wajibPajak?->alamat,
            'nama_objek' => $t->objekPajak?->nama,
            'nopd' => $t->objekPajak?->nopd,
            'alamat' => $t->objekPajak?->alamat,
            'rekening' => $t->objekPajak?->rekening?->kode ?? '4.1.1.01.03.00.00.00',
            'no_sptpd' => $t->no_sspd ?? $t->kode_billing,
            'masa_pajak' => (int) $t->masa_awal->month,
            'tahun' => (int) $t->masa_awal->year,
            'pokok_pajak' => $t->pokok_pajak,
            'kode_bayar' => $t->kode_billing,
            'kode_bayar_url' => null,
            'jatuh_tempo' => self::tanggal($t->tgl_jatuh_tempo),
            'denda' => (int) ($t->final_denda ?? 0),
            'total_tagihan' => $t->total_tagihan,
        ];
    }
}
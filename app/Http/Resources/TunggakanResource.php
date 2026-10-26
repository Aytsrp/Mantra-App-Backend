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
            'nama_objek' => $t->objekPajak?->nama,
            'alamat' => $t->objekPajak?->alamat,
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
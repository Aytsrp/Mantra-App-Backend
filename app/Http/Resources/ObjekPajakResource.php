<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ObjekPajakResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $o = $this->resource;
        $lap = $o->relationLoaded('laporanPeriode') ? $o->getRelation('laporanPeriode') : null;

        return [
            'id' => $o->uuid,
            'npwpd' => $o->wajibPajak?->npwpd,
            'nama_wp' => $o->wajibPajak?->nama,
            'nopd' => $o->nopd,
            'nama' => $o->nama,
            'alamat' => $o->alamat,
            'jenis_pajak' => $o->jenisPajak?->kode,
            'nama_jenis_pajak' => $o->jenisPajak?->nama,
            'rekening' => $o->rekening?->kode,
            'nama_rekening' => $o->rekening?->nama,
            'tarif_persen' => $o->rekening?->tarif_persen,
            'tgl_akhir_lapor' => $o->jenisPajak?->tgl_akhir_lapor,
            'keterangan' => null,
            'no_telp' => $o->wajibPajak?->no_telp ?: $o->wajibPajak?->no_hp,
            'aktif' => (bool) $o->is_active,
            'masa_pajak' => $o->getAttribute('periode_masa'),
            'tahun' => $o->getAttribute('periode_tahun'),
            'sudah_lapor' => $lap !== null,
            'laporan_id' => $lap?->uuid,
        ];
    }
}
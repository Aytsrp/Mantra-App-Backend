<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ProfilResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $u = $this->resource;
        $wp = $u->wajibPajak;
        $foto = $u->foto && config('mantra.foto_base_url')
            ? rtrim(config('mantra.foto_base_url'), '/').'/'.ltrim($u->foto, '/')
            : null;

        return [
            'id' => $u->uuid,
            'username' => $u->username,
            'nama' => $u->name,
            'email' => $u->email,
            'no_hp' => $u->phone !== '' ? $u->phone : ($wp?->no_hp ?? ''),
            'wa_terverifikasi' => false,
            'alamat' => $wp?->alamat ?: $u->address,
            'foto_url' => $foto,
            'npwpd' => $wp?->npwpd,
            'nama_wp' => $wp?->nama,
        ];
    }
}
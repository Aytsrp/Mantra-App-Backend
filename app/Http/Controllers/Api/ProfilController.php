<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfilResource;
use App\Models\PengaturanPengguna;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('wajibPajak');

        return ApiResponse::ok((new ProfilResource($user))->resolve($request));
    }

    public function ditunda(): never
    {
        throw ApiException::belumTersedia('Fitur ini');
    }

    public function pengaturan(Request $request): JsonResponse
    {
        return ApiResponse::ok($this->bentuk($this->ambil($request)));
    }

    public function ubahPengaturan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mode_gelap' => ['sometimes', 'boolean'],
            'bahasa' => ['sometimes', 'string', 'in:id,en'],
            'notifikasi' => ['sometimes', 'boolean'],
            'pengingat' => ['sometimes', 'boolean'],
        ]);

        $p = $this->ambil($request);
        $p->fill($data)->save();

        return ApiResponse::ok($this->bentuk($p));
    }

    private function ambil(Request $request): PengaturanPengguna
    {
        return PengaturanPengguna::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['mode_gelap' => false, 'bahasa' => 'id', 'notifikasi' => true, 'pengingat' => true],
        );
    }

    private function bentuk(PengaturanPengguna $p): array
    {
        return [
            'mode_gelap' => (bool) $p->mode_gelap,
            'bahasa' => $p->bahasa,
            'notifikasi' => (bool) $p->notifikasi,
            'pengingat' => (bool) $p->pengingat,
        ];
    }
}
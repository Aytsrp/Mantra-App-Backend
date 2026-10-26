<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisPajak;
use App\Models\ObjekPajak;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JenisPajakController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $idWp = (int) $request->attributes->get('wajib_pajak')->id;

        $rows = JenisPajak::query()
            ->whereIn('id', ObjekPajak::query()->milik($idWp)->aktif()->select('id_jenis_pajak'))
            ->orderBy('kode')
            ->get()
            ->map(fn ($j) => [
                'kode' => $j->kode,
                'nama' => $j->nama,
                'nama_singkat' => $j->nama_singkat,
            ])->all();

        return ApiResponse::ok($rows);
    }
}
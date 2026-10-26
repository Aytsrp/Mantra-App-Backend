<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\LaporanResource;
use App\Models\Transaksi;
use App\Support\ApiResponse;
use App\Support\ListParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    private const SORT = [
        'tanggal' => 'tgl_pendataan',
        'masa_pajak' => 'masa_awal',
        'jatuh_tempo' => 'tgl_jatuh_tempo',
        'pokok_pajak' => 'jumlah_pajak',
    ];

    public function index(Request $request): JsonResponse
    {
        $p = ListParams::from($request);

        $query = Transaksi::query()
            ->milik((int) $request->attributes->get('wajib_pajak')->id)
            ->aktif()->sptpd()
            ->filterJenis($p->jenisPajak)->cari($p->q)
            ->with(['objekPajak', 'wajibPajak']);

        $page = $p->urutkan($query, self::SORT, '-tanggal')->paginate($p->perPage, ['*'], 'page', $p->page);

        return ApiResponse::paginated($page, LaporanResource::daftar($page->getCollection()));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $trx = Str::isUuid($id)
            ? Transaksi::query()
                ->milik((int) $request->attributes->get('wajib_pajak')->id)
                ->aktif()->where('uuid', $id)
                ->with(['objekPajak', 'wajibPajak'])->first()
            : null;

        return ApiResponse::ok((new LaporanResource($trx ?? throw ApiException::notFound()))->resolve($request));
    }

    public function store(): never
    {
        throw ApiException::belumTersedia('Kirim laporan SPTPD');
    }
}
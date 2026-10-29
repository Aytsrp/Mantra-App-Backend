<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TunggakanResource;
use App\Models\Transaksi;
use App\Support\ApiResponse;
use App\Support\ListParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TunggakanController extends Controller
{
    private const SORT = [
        'tanggal' => 'tgl_pendataan',
        'jatuh_tempo' => 'tgl_jatuh_tempo',
        'masa_pajak' => 'masa_awal',
        'pokok_pajak' => 'jumlah_pajak',
    ];

    public function index(Request $request): JsonResponse
    {
        $p = ListParams::from($request);

        $query = Transaksi::query()
            ->milik((int) $request->attributes->get('wajib_pajak')->id)
            ->aktif()->tunggakan()
            ->filterJenis($p->jenisPajak)->cari($p->q)
            ->with(['objekPajak.rekening', 'wajibPajak']);

        $page = $p->urutkan($query, self::SORT, '-tanggal')->paginate($p->perPage, ['*'], 'page', $p->page);

        return ApiResponse::paginated($page, TunggakanResource::daftar($page->getCollection()));
    }
}
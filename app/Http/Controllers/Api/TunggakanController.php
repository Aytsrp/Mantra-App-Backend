<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TunggakanResource;
use App\Models\Transaksi;
use App\Support\ApiResponse;
use App\Support\ListParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        $idWp = (int) $request->attributes->get('wajib_pajak')->id;
        $p = ListParams::from($request);
        $cacheKey = "tunggakan:list:{$idWp}:{$p->page}:{$p->perPage}:" . md5(($p->jenisPajak ?? '') . ($p->q ?? '') . $request->get('sort', '-tanggal'));

        $result = Cache::remember($cacheKey, 60, function () use ($idWp, $p) {
            $query = Transaksi::query()
                ->milik($idWp)
                ->aktif()->tunggakan()
                ->filterJenis($p->jenisPajak)->cari($p->q)
                ->with(['objekPajak.rekening', 'wajibPajak']);

            $page = $p->urutkan($query, self::SORT, '-tanggal')->paginate($p->perPage, ['*'], 'page', $p->page);

            return [
                'meta' => [
                    'page' => $page->currentPage(),
                    'per_page' => $page->perPage(),
                    'total' => $page->total(),
                    'total_pages' => $page->lastPage(),
                ],
                'data' => TunggakanResource::daftar($page->getCollection()),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }
}
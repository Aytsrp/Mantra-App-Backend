<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranResource;
use App\Models\Pembayaran;
use App\Support\ApiResponse;
use App\Support\ListParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PembayaranController extends Controller
{
    private const SORT = ['tanggal' => 'tanggal'];

    public function index(Request $request): JsonResponse
    {
        $idWp = (int) $request->attributes->get('wajib_pajak')->id;
        $p = ListParams::from($request);
        $cacheKey = "pembayaran:list:{$idWp}:{$p->page}:{$p->perPage}:" . md5(($p->jenisPajak ?? '') . ($p->q ?? '') . $request->get('sort', '-tanggal'));

        $result = Cache::remember($cacheKey, 60, function () use ($idWp, $p) {
            $query = Pembayaran::query()
                ->milik($idWp)
                ->filterJenis($p->jenisPajak)->cari($p->q)
                ->with(['transaksi', 'objekPajak.rekening', 'wajibPajak', 'jenisPajak'])
                ->withSum('detail as jumlah_dibayar', 'jumlah_pembayaran');

            $page = $p->urutkan($query, self::SORT, '-tanggal')->paginate($p->perPage, ['*'], 'page', $p->page);

            return [
                'meta' => [
                    'page' => $page->currentPage(),
                    'per_page' => $page->perPage(),
                    'total' => $page->total(),
                    'total_pages' => $page->lastPage(),
                ],
                'data' => PembayaranResource::daftar($page->getCollection()),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }
}
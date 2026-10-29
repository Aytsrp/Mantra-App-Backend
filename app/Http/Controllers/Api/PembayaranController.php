<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranResource;
use App\Models\Pembayaran;
use App\Support\ApiResponse;
use App\Support\ListParams;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    private const SORT = ['tanggal' => 'tanggal'];

    public function index(Request $request): JsonResponse
    {
        $p = ListParams::from($request);

        $query = Pembayaran::query()
            ->milik((int) $request->attributes->get('wajib_pajak')->id)
            ->filterJenis($p->jenisPajak)->cari($p->q)
            ->with(['transaksi', 'objekPajak.rekening', 'wajibPajak', 'jenisPajak'])
            ->withSum('detail as jumlah_dibayar', 'jumlah_pembayaran');

        $page = $p->urutkan($query, self::SORT, '-tanggal')->paginate($p->perPage, ['*'], 'page', $p->page);

        return ApiResponse::paginated($page, PembayaranResource::daftar($page->getCollection()));
    }
}
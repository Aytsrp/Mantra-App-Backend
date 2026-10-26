<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ObjekPajakResource;
use App\Http\Resources\RiwayatResource;
use App\Models\ObjekPajak;
use App\Models\Transaksi;
use App\Support\ApiResponse;
use App\Support\ListParams;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ObjekPajakController extends Controller
{
    private const SORT = ['nopd' => 'nopd', 'nama' => 'nama'];

    public function index(Request $request): JsonResponse
    {
        $idWp = (int) $request->attributes->get('wajib_pajak')->id;
        $p = ListParams::from($request);
        [$bulan, $tahun] = $this->periode($request);

        $query = ObjekPajak::query()->milik($idWp)->aktif()
            ->filterJenis($p->jenisPajak)->cari($p->q)
            ->with(['wajibPajak', 'jenisPajak', 'rekening']);

        $page = $p->urutkan($query, self::SORT, 'nopd')->paginate($p->perPage, ['*'], 'page', $p->page);

        $this->isiPeriode($page->getCollection(), $bulan, $tahun);

        return ApiResponse::paginated($page, ObjekPajakResource::daftar($page->getCollection()));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $objek = $this->cari($request, $id)->load(['wajibPajak', 'jenisPajak', 'rekening']);
        [$bulan, $tahun] = $this->periode($request);
        $this->isiPeriode(collect([$objek]), $bulan, $tahun);

        return ApiResponse::ok((new ObjekPajakResource($objek))->resolve($request));
    }

    public function riwayat(Request $request, string $id): JsonResponse
    {
        $objek = $this->cari($request, $id);
        $tahun = (int) $request->query('tahun', now()->year);

        $rows = Transaksi::query()
            ->where('id_objek_pajak', $objek->id)
            ->milik($objek->id_wajib_pajak)->aktif()->sptpd()
            ->whereBetween('masa_awal', ["$tahun-01-01", "$tahun-12-31"])
            ->with('pembayaran.detail')
            ->orderBy('masa_awal')->orderBy('id')
            ->get();

        return ApiResponse::ok(RiwayatResource::daftar($rows));
    }

    private function cari(Request $request, string $id): ObjekPajak
    {
        if (! Str::isUuid($id)) {
            throw ApiException::notFound();
        }

        return ObjekPajak::query()
            ->milik((int) $request->attributes->get('wajib_pajak')->id)
            ->where('uuid', $id)
            ->first() ?? throw ApiException::notFound();
    }

    /** @return array{0:int,1:int} [bulan, tahun] */
    private function periode(Request $request): array
    {
        $default = CarbonImmutable::now()->startOfMonth()->subMonths((int) config('mantra.offset_bulan_lapor'));
        $bulan = (int) $request->query('masa_pajak', $default->month);
        $tahun = (int) $request->query('tahun', $default->year);

        if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
            return [$default->month, $default->year];
        }

        return [$bulan, $tahun];
    }

    private function isiPeriode($objekList, int $bulan, int $tahun): void
    {
        $awal = CarbonImmutable::create($tahun, $bulan, 1);
        $laporan = Transaksi::query()
            ->whereIn('id_objek_pajak', $objekList->pluck('id'))
            ->aktif()->sptpd()
            ->whereBetween('masa_awal', [$awal->toDateString(), $awal->endOfMonth()->toDateString()])
            ->orderBy('id')
            ->get()
            ->keyBy('id_objek_pajak');

        foreach ($objekList as $objek) {
            $objek->setAttribute('periode_masa', $bulan);
            $objek->setAttribute('periode_tahun', $tahun);
            $objek->setRelation('laporanPeriode', $laporan->get($objek->id));
        }
    }
}
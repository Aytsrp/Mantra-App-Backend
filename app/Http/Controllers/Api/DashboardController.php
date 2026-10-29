<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LaporanResource;
use App\Http\Resources\PembayaranResource;
use App\Models\ObjekPajak;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private const JUMLAH_AKTIVITAS = 5;

    public function ringkasan(Request $request): JsonResponse
    {
        $idWp = (int) $request->attributes->get('wajib_pajak')->id;

        $data = Cache::remember("dashboard:ringkasan:{$idWp}", 60, function () use ($request, $idWp) {
            $tunggakanAgg = Transaksi::query()
                ->milik($idWp)
                ->aktif()
                ->tunggakan()
                ->selectRaw('COALESCE(SUM('.Transaksi::TOTAL_TAGIHAN_SQL.'), 0) AS total, COUNT(*) AS jumlah')
                ->first();

            $total = (int) ($tunggakanAgg->total ?? 0);
            $jumlah = (int) ($tunggakanAgg->jumlah ?? 0);

            $objekAktif = ObjekPajak::query()->milik($idWp)->aktif()->count();

            $laporan = Transaksi::query()->milik($idWp)->aktif()->sptpd()
                ->with(['objekPajak', 'wajibPajak'])
                ->orderByDesc('created_at')->orderByDesc('id')
                ->limit(self::JUMLAH_AKTIVITAS)->get();

            $bayar = Pembayaran::query()->milik($idWp)
                ->with(['transaksi', 'objekPajak', 'wajibPajak', 'jenisPajak'])
                ->withSum('detail as jumlah_dibayar', 'jumlah_pembayaran')
                ->orderByDesc('created_at')->orderByDesc('id')
                ->limit(self::JUMLAH_AKTIVITAS)->get();

            $aktivitas = collect()
                ->merge($laporan->map(fn ($t) => [
                    'id' => $t->uuid,
                    'tipe' => 'laporan',
                    'waktu' => $t->created_at?->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
                    'jenis_pajak' => $t->nama_jenis_pajak,
                    'nama_objek' => $t->objekPajak?->nama,
                    'masa_pajak' => (int) $t->masa_awal->month,
                    'tahun' => (int) $t->masa_awal->year,
                    'jumlah' => $t->pokok_pajak,
                ]))
                ->merge($bayar->map(fn ($p) => [
                    'id' => $p->uuid,
                    'tipe' => 'pembayaran',
                    'waktu' => ($p->created_at ?? $p->tanggal)?->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
                    'jenis_pajak' => $p->transaksi?->nama_jenis_pajak ?? $p->jenisPajak?->nama,
                    'nama_objek' => $p->objekPajak?->nama,
                    'masa_pajak' => $p->transaksi ? (int) $p->transaksi->masa_awal->month : null,
                    'tahun' => $p->transaksi ? (int) $p->transaksi->masa_awal->year : null,
                    'jumlah' => (int) ($p->getAttribute('jumlah_dibayar') ?? 0),
                ]))
                ->sortByDesc('waktu')->take(self::JUMLAH_AKTIVITAS)->values()->all();

            return [
                'total_tunggakan' => $total,
                'jumlah_tunggakan' => $jumlah,
                'objek_pajak_aktif' => $objekAktif,
                'laporan_terakhir' => $laporan->isEmpty() ? null : (new LaporanResource($laporan->first()))->resolve($request),
                'pembayaran_terakhir' => $bayar->isEmpty() ? null : (new PembayaranResource($bayar->first()))->resolve($request),
                'aktivitas_terakhir' => $aktivitas,
            ];
        });

        return ApiResponse::ok($data);
    }
}
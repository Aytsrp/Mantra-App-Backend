<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class ListParams
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $q,
        public readonly ?string $jenisPajak,
        public readonly ?string $sort,
    ) {}

    public static function from(Request $r): self
    {
        $str = static fn (string $k): ?string => is_string($v = $r->query($k)) && $v !== '' ? $v : null;

        return new self(
            page: max(1, (int) $r->query('page', 1)),
            perPage: min(50, max(1, (int) $r->query('per_page', 10))),
            q: $str('q'),
            jenisPajak: $str('jenis_pajak'),
            sort: $str('sort'),
        );
    }

    /** @param array<string,string> $peta nama sort API => kolom tabel */
    public function urutkan(Builder $query, array $peta, string $default): Builder
    {
        $sort = $this->sort ?? $default;
        $desc = str_starts_with($sort, '-');
        $kunci = ltrim($sort, '-');

        if (! isset($peta[$kunci])) {
            $desc = str_starts_with($default, '-');
            $kunci = ltrim($default, '-');
        }

        return $query->orderBy($peta[$kunci], $desc ? 'desc' : 'asc')->orderBy('id', 'desc');
    }
}
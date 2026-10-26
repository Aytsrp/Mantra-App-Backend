<?php

namespace App\Http\Resources;

use Carbon\CarbonInterface;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiResource extends JsonResource
{
    public static $wrap = null;

    /** @return array<int,array<string,mixed>> */
    public static function daftar(iterable $items): array
    {
        $out = [];
        foreach ($items as $item) {
            $out[] = (new static($item))->resolve(request());
        }

        return $out;
    }

    protected static function tanggal(?CarbonInterface $d): ?string
    {
        return $d?->toDateString();
    }

    protected static function waktu(?CarbonInterface $d): ?string
    {
        return $d?->copy()->utc()->format('Y-m-d\TH:i:s\Z');
    }
}
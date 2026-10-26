<?php

return [
    'wajib_pajak_role_id' => (int) env('MANTRA_ROLE_WAJIB_PAJAK', 1),
    'access_ttl_minutes' => (int) env('MANTRA_ACCESS_TTL_MINUTES', 60),
    'refresh_ttl_days' => (int) env('MANTRA_REFRESH_TTL_DAYS', 30),
    'jenis_surat_sptpd' => array_values(array_filter(
        array_map('intval', explode(',', (string) env('MANTRA_JENIS_SURAT_SPTPD', ''))),
    )),
    'offset_bulan_lapor' => (int) env('MANTRA_OFFSET_BULAN_LAPOR', 1),
    'foto_base_url' => env('MANTRA_FOTO_BASE_URL'),
];
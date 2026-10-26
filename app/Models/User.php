<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $connection = 'mantra';

    protected $table = 'users';

    protected $guarded = ['*'];

    protected $hidden = ['password', 'password_md5', 'remember_token'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_md5' => 'boolean',
        ];
    }

    public function wajibPajak(): BelongsTo
    {
        return $this->belongsTo(WajibPajak::class, 'id_wajib_pajak');
    }

    public function passwordCocok(string $plain): bool
    {
        if ($this->is_md5) {
            return $this->password_md5 !== null
                && hash_equals(strtolower($this->password_md5), md5($plain));
        }

        try {
            return Hash::check($plain, (string) $this->password);
        } catch (Throwable) {
            return false;
        }
    }

    public function bolehMasukMobile(): bool
    {
        return $this->is_active
            && (int) $this->role_id === (int) config('mantra.wajib_pajak_role_id')
            && $this->id_wajib_pajak !== null;
    }
}
<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function login(string $username, string $password): User
    {
        $kandidat = User::query()
            ->whereRaw('LOWER(username) = ?', [mb_strtolower(trim($username))])
            ->where('is_active', true)
            ->where('role_id', config('mantra.wajib_pajak_role_id'))
            ->whereNotNull('id_wajib_pajak')
            ->limit(5)
            ->get();

        $cocok = $kandidat->filter(fn (User $u) => $u->passwordCocok($password))->values();

        if ($cocok->count() !== 1) {
            throw ApiException::unauthenticated('Username atau password salah');
        }

        return $cocok->first();
    }

    /** @return array{access_token:string,refresh_token:string,expires_in:int} */
    public function terbitkanToken(User $user): array
    {
        $ttl = (int) config('mantra.access_ttl_minutes');

        $access = $user->createToken('access', ['access'], now()->addMinutes($ttl));
        $refresh = $user->createToken(
            'refresh:'.$access->accessToken->id,
            ['refresh'],
            now()->addDays((int) config('mantra.refresh_ttl_days')),
        );

        return [
            'access_token' => $access->plainTextToken,
            'refresh_token' => $refresh->plainTextToken,
            'expires_in' => $ttl * 60,
        ];
    }

    public function refresh(string $refreshToken): array
    {
        $token = PersonalAccessToken::findToken($refreshToken);
        $user = $token?->tokenable;

        if (! $token
            || ! $token->can('refresh')
            || ($token->expires_at && $token->expires_at->isPast())
            || ! $user instanceof User
            || ! $user->bolehMasukMobile()) {
            throw ApiException::unauthenticated();
        }

        $this->cabutPasangan($user, $token);

        return $this->terbitkanToken($user);
    }

    public function logout(User $user, PersonalAccessToken $current): void
    {
        $this->cabutPasangan($user, $current);
    }

    private function cabutPasangan(User $user, PersonalAccessToken $token): void
    {
        if (str_starts_with($token->name, 'refresh:')) {
            $user->tokens()->whereKey((int) substr($token->name, 8))->delete();
        } else {
            $user->tokens()->where('name', 'refresh:'.$token->id)->delete();
        }

        $token->delete();
    }
}
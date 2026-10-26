<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status,
        public readonly ?array $fields = null,
    ) {
        parent::__construct($message);
    }

    public static function unauthenticated(string $message = 'Sesi tidak valid, silakan masuk kembali'): self
    {
        return new self('UNAUTHENTICATED', $message, 401);
    }

    public static function notFound(string $message = 'Data tidak ditemukan'): self
    {
        return new self('NOT_FOUND', $message, 404);
    }

    public static function belumTersedia(string $fitur): self
    {
        return new self('NOT_IMPLEMENTED', "$fitur belum tersedia", 501);
    }
}
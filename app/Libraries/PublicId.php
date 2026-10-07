<?php
namespace App\Libraries;

use RuntimeException;

/**
 * Identifier publik untuk URL dan request CRUD.
 *
 * Nilai database tidak diubah. Token bersifat terenkripsi dan dilindungi
 * autentikasi (AES-256-GCM), sehingga tidak dapat ditebak atau dimodifikasi
 * tanpa secret aplikasi.
 */
final class PublicId
{
    private const VERSION = 'p1';
    private const CIPHER = 'aes-256-gcm';
    private const IV_BYTES = 12;

    private function __construct()
    {
    }

    public static function encode($value, string $context = 'id'): string
    {
        $secret = self::secret();
        $iv = random_bytes(self::IV_BYTES);
        $plain = json_encode([
            'v' => 1,
            'c' => $context,
            'i' => (string) $value,
        ], JSON_UNESCAPED_SLASHES);
        if ($plain === false) {
            throw new RuntimeException('Identifier tidak dapat dibuat.');
        }

        $tag = '';
        $cipher = openssl_encrypt(
            $plain,
            self::CIPHER,
            $secret,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::aad($context),
            16
        );
        if ($cipher === false || strlen($tag) !== 16) {
            throw new RuntimeException('Identifier tidak dapat dienkripsi.');
        }

        return self::VERSION . '.' . self::base64url($iv . $tag . $cipher);
    }

    public static function decode(string $token, string $context = 'id'): ?string
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || $parts[0] !== self::VERSION || $parts[1] === '') {
            return null;
        }
        $raw = self::base64urlDecode($parts[1]);
        if ($raw === null || strlen($raw) <= self::IV_BYTES + 16) {
            return null;
        }

        $iv = substr($raw, 0, self::IV_BYTES);
        $tag = substr($raw, self::IV_BYTES, 16);
        $cipher = substr($raw, self::IV_BYTES + 16);
        $plain = openssl_decrypt(
            $cipher,
            self::CIPHER,
            self::secret(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::aad($context)
        );
        if ($plain === false) {
            return null;
        }

        $data = json_decode($plain, true);
        if (!is_array($data) || ($data['v'] ?? null) !== 1 || ($data['c'] ?? null) !== $context) {
            return null;
        }
        return isset($data['i']) && is_scalar($data['i']) ? (string) $data['i'] : null;
    }

    private static function secret(): string
    {
        $secret = (string) env('app.publicIdKey');
        if ($secret === '') {
            throw new RuntimeException('app.publicIdKey belum dikonfigurasi.');
        }
        return hash('sha256', $secret, true);
    }

    private static function aad(string $context): string
    {
        return 'inventory-public-id:' . $context;
    }

    private static function base64url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $value): ?string
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9_-]+$/D', $value) !== 1) {
            return null;
        }
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return $decoded === false ? null : $decoded;
    }
}

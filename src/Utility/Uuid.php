<?php
declare(strict_types=1);

namespace App\Utility;

use DateTimeInterface;

/**
 * UUID version 7 (RFC 9562): 48-bit Unix millisecond timestamp followed by
 * random bits. Time-ordered, so they index well and sort by creation time.
 */
final class Uuid
{
    public const PATTERN = '[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}';

    /**
     * Generate a UUIDv7.
     *
     * @param \DateTimeInterface|null $at Timestamp to embed; now when null. Backfills
     *   pass the row's creation time so old rows keep their chronological order.
     * @return string Lowercase canonical form, e.g. 01923f4e-8a7b-7c3d-9e1f-2a3b4c5d6e7f.
     */
    public static function v7(?DateTimeInterface $at = null): string
    {
        $ms = $at === null
            ? (int)floor(microtime(true) * 1000)
            : (int)$at->format('Uv');

        // 6 bytes big-endian timestamp + 10 random bytes.
        $bytes = substr(pack('J', $ms), 2) . random_bytes(10);
        // Version 7 in the high nibble of byte 6, RFC variant (10xx) in byte 8.
        $bytes[6] = chr(0x70 | (ord($bytes[6]) & 0x0F));
        $bytes[8] = chr(0x80 | (ord($bytes[8]) & 0x3F));

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }

    /**
     * Whether the string is a canonical lowercase UUIDv7.
     *
     * @param string $value Value to check.
     * @return bool
     */
    public static function isValid(string $value): bool
    {
        return preg_match('/^' . self::PATTERN . '$/D', $value) === 1;
    }
}

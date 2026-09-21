<?php
declare(strict_types=1);

namespace App\Test\TestCase\Utility;

use App\Utility\Uuid;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Utility\Uuid
 */
class UuidTest extends TestCase
{
    public function testFormatAndVersion(): void
    {
        $uuid = Uuid::v7();

        $this->assertTrue(Uuid::isValid($uuid), $uuid);
        $this->assertSame('7', $uuid[14], 'version nibble');
        $this->assertContains($uuid[19], ['8', '9', 'a', 'b'], 'RFC variant');
    }

    public function testEmbedsGivenTimestamp(): void
    {
        $at = new DateTime('2026-09-18 17:21:11.123', 'UTC');
        $uuid = Uuid::v7($at);

        $ms = hexdec(str_replace('-', '', substr($uuid, 0, 13)));
        $this->assertSame((int)$at->format('Uv'), $ms);
    }

    public function testSortsByTime(): void
    {
        $older = Uuid::v7(new DateTime('2026-01-01 00:00:00'));
        $newer = Uuid::v7(new DateTime('2026-01-01 00:00:01'));

        $this->assertLessThan(0, strcmp($older, $newer));
    }

    public function testUnique(): void
    {
        $uuids = array_map(fn() => Uuid::v7(), range(1, 1000));

        $this->assertCount(1000, array_unique($uuids));
    }

    public function testIsValid(): void
    {
        $this->assertFalse(Uuid::isValid('not-a-uuid'));
        // v4, not v7.
        $this->assertFalse(Uuid::isValid('f47ac10b-58cc-4372-a567-0e02b2c3d479'));
        $this->assertFalse(Uuid::isValid(strtoupper(Uuid::v7())));
    }
}

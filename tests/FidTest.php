<?php
namespace Fortifi\Fid\Tests;

use Fortifi\Fid\Fid;
use PHPUnit\Framework\TestCase;

class FidTest extends TestCase
{
  public function testFidCompress()
  {
    $initialFid = 'FID:PCHS:SUBS:1579213639:yaDVfRt';

    $compressed = Fid::compress($initialFid);
    static::assertEquals('q4811j-yaDVfRt', $compressed);

    $expanded = Fid::expand($compressed, 'PCHS', 'SUBS');
    static::assertEquals($initialFid, $expanded);

    $compressed = Fid::compress($initialFid, false);
    static::assertEquals('PCHS-SUBS-q4811j-yaDVfRt', $compressed);

    $expanded = Fid::expand($compressed);
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expand($compressed, 'PCHS', 'SUBS');
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expand($compressed, 'EHE', 'WEHE');
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expand($compressed, 'EHE', 'WEHE', false);
    static::assertNotEquals($initialFid, $expanded);
  }

  public function testShortFidCompress()
  {
    $initialFid = 'FID:TKT:1579119890:Iu5jsrTEHhctx';

    $compressed = Fid::compress($initialFid);
    static::assertEquals('q460pe-Iu5jsrTEHhctx', $compressed);

    $expanded = Fid::expand($compressed, 'TKT', 'TKT');
    static::assertEquals($initialFid, $expanded);

    $compressed = Fid::compress($initialFid, false);
    static::assertEquals('TKT-q460pe-Iu5jsrTEHhctx', $compressed);

    $expanded = Fid::expand($compressed);
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expand($compressed, 'TKT', 'TKT');
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expand($compressed, 'TKT', 'EHE');
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expand($compressed, 'TKT');
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expand($compressed, 'EHE', 'WEHE');
    static::assertNotEquals($initialFid, $expanded);

    $expanded = Fid::expand($compressed, 'EHE', 'WEHE', false);
    static::assertNotEquals($initialFid, $expanded);
  }

  public function testUnexpectedCompress()
  {
    $initialFid = 'FID:PCHS:SUBS:1579213639:yaDVfRt';
    $compressedShort = 'q4811j-yaDVfRt';
    $compressedLong = 'PCHS-SUBS-q4811j-yaDVfRt';

    static::assertEquals($initialFid, Fid::expand($initialFid));
    static::assertEquals($initialFid, Fid::expand($initialFid, 'CHJ'));
    static::assertEquals($initialFid, Fid::expand($initialFid, 'CHJ', 'XD'));
    static::assertEquals($initialFid, Fid::expand($initialFid, 'CHJ', 'XD', false));

    static::assertEquals($compressedShort, Fid::compress($compressedShort));
    static::assertEquals($compressedShort, Fid::compress($compressedShort, false));

    static::assertEquals($compressedLong, Fid::compress($compressedLong));
    static::assertEquals($compressedLong, Fid::compress($compressedLong, false));
  }

  public function testUrlCompressed()
  {
    $initialFid = 'FID:PCHS:SUBS:1579213639:yaDVfRt';

    $compressed = Fid::compressForUrl($initialFid);
    static::assertEquals('q4811j-9cemohx3rec', $compressed);

    $expanded = Fid::expandFromUrl($compressed, 'PCHS', 'SUBS');
    static::assertEquals($initialFid, $expanded);

    $compressed = Fid::compressForUrl($initialFid, false);
    static::assertEquals('PCHS-SUBS-q4811j-9cemohx3rec', $compressed);

    $expanded = Fid::expandFromUrl($compressed);
    var_dump($expanded);
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expandFromUrl($compressed, 'PCHS', 'SUBS');
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expandFromUrl($compressed, 'EHE', 'WEHE');
    static::assertEquals($initialFid, $expanded);

    $expanded = Fid::expandFromUrl($compressed, 'EHE', 'WEHE', false);
    static::assertNotEquals($initialFid, $expanded);
  }
}

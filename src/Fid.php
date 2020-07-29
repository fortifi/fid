<?php
namespace Fortifi\Fid;

use Exception;
use InvalidArgumentException;

class Fid
{
  /**
   * @param string $fid
   *
   * @return string
   *
   * @throws InvalidArgumentException
   */
  public static function getType($fid)
  {
    $parts = explode(':', $fid);
    switch(count($parts))
    {
      case 5:
      case 4:
        return $parts[1];
      default:
        throw new InvalidArgumentException("Invalid FID Passed '$fid'", 500);
    }
  }

  /**
   * @param string $fid
   *
   * @return string
   *
   * @throws InvalidArgumentException
   */
  public static function getSubType($fid)
  {
    $parts = explode(':', $fid);
    switch(count($parts))
    {
      case 5:
        return $parts[2];
      case 4:
        return $parts[1];
      default:
        throw new InvalidArgumentException("Invalid FID Passed '$fid'", 500);
    }
  }

  /**
   * @param string $fid
   *
   * @return array
   *
   * @throws InvalidArgumentException
   */
  public static function getTypes($fid)
  {
    $parts = explode(':', $fid);
    switch(count($parts))
    {
      case 5:
        return [$parts[1], $parts[2]];
      case 4:
        return [$parts[1], $parts[1]];
      default:
        throw new InvalidArgumentException("Invalid FID Passed '$fid'", 500);
    }
  }

  public static function getFullType($fid)
  {
    return implode('_', static::getTypes($fid));
  }

  /**
   * @param string $fid
   *
   * @return int
   */
  public static function getTime($fid)
  {
    $fp = explode(':', $fid);
    if(count($fp) > 2)
    {
      array_pop($fp);
      return array_pop($fp);
    }
    return 0;
  }

  /**
   * @param string     $fid
   * @param bool|false $quick
   *
   * @return bool
   */
  public static function isFid($fid, $quick = false)
  {
    if(is_string($fid) && substr($fid, 0, 4) === 'FID:')
    {
      $c = substr_count($fid, ':');
      return $c == 3 || $c == 4;
    }
    else if($quick || $fid === null)
    {
      return false;
    }

    if(is_object($fid))
    {
      $e = new Exception('attempting to use ' . get_class($fid) . ' in fid check');
      error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    }
    else if(!is_string($fid))
    {
      $e = new Exception('attempting to use ' . gettype($fid) . ' in fid check');
      error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    }

    return false;
  }

  public static function isType($fid, $type)
  {
    return static::isFid($fid, true) && static::getType($fid) == $type;
  }

  public static function isSubType($fid, $subType)
  {
    return static::isFid($fid, true) && static::getSubType($fid) == $subType;
  }

  public static function isFullType($fid, $fullType)
  {
    return static::isFid($fid, true) && static::getFullType($fid) == $fullType;
  }

  public static function compress($fid, $stripTypes = true)
  {
    $parts = explode(':', $fid);
    //Trim FID
    $fidCheck = array_shift($parts);
    if($fidCheck !== 'FID' || count($parts) < 3)
    {
      return $fid;
    }

    $compressed = [array_pop($parts)];
    array_unshift($compressed, base_convert(array_pop($parts), 10, 36));

    if(!$stripTypes)
    {
      $subType = array_pop($parts);
      $type = !empty($parts) ? array_pop($parts) : $subType;
      array_unshift($compressed, $subType);
      if($type !== $subType)
      {
        array_unshift($compressed, $type);
      }
    }

    return implode('-', $compressed);
  }

  public static function expand($compressedFid, $type = null, $subType = null, $preferCompressedType = true)
  {
    $parts = explode('-', $compressedFid);
    if(count($parts) < 2)
    {
      return $compressedFid;
    }
    $unique = array_pop($parts);
    $time = base_convert(array_pop($parts), 36, 10);
    if(!empty($parts) && ($subType === null || $preferCompressedType))
    {
      $subType = array_pop($parts);
    }
    if($type === null || (!empty($parts) && $preferCompressedType))
    {
      $type = empty($parts) ? $subType : array_pop($parts);
    }

    return 'FID:' . $type . ($subType !== $type ? ':' . $subType : '') . ':' . $time . ':' . $unique;
  }

  public static function compressForUrl($fid, $stripTypes = true)
  {
    $compressed = static::compress($fid, $stripTypes);
    $exploded = explode('-', $compressed);
    $unique = array_pop($exploded);
    foreach(str_split($unique, 8) as $un)
    {
      array_push($exploded, strtolower(base_convert(bin2hex($un), 16, 36)));
    }
    return implode('-', $exploded);
  }

  public static function expandFromUrl($compressedFid, $type = null, $subType = null, $preferCompressedType = true)
  {
    $exploded = explode('-', $compressedFid);
    $time = array_shift($exploded);
    $unique = '';
    foreach($exploded as $part)
    {
      $unique .= hex2bin(base_convert($part, 36, 16));
    }
    return static::expand($time . '-' . $unique, $type, $subType, $preferCompressedType);
  }

}

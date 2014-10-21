<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 14-9-16
 * Time: 下午12:14
 */

namespace Jason\Util;


class ImageCheacker
{
//    const PNG = 'image/png';
//    const JPG = 'image/jpeg';
//    const GIF = 'image/gif';
    const PNG = 1;
    const JPG = 2;
    const GIF = 3;

    const ERROR_OUTOFSIZE = 200;
    const ERROR_EXTENSION = 300;

    /**
     *
     * @param $maxSize KB(*1024)
     * @param $imgExt
     */
    public static function uploadImgChecker($file, $maxSize, $imgExt)
    {
        $needleSize = $maxSize * 1024;
        if ($file['size'] > $needleSize) {
            return self::ERROR_OUTOFSIZE;
        }
        if (is_array($imgExt)) {
            foreach ($imgExt as $one) {
                if (true === self::imgAllCheck($one, $file)) {
                    return true;
                }
            }
            return self::ERROR_EXTENSION;
        } else {
            return self::imgAllCheck($imgExt, $file);
        }
        return self::ERROR_EXTENSION;
    }

    private static function imgAllCheck($ext, $file)
    {
        switch ($ext) {
            case self::PNG:
                return self::PNGChecker($file);
            case self::JPG:
                return self::JPEGChecker($file);
            case self::GIF;
                return self::GIFChecker($file);
        }
    }

    private static function PNGChecker($file)
    {
        $pathInfo = pathinfo($file['name']);
        if (strtolower($pathInfo['extension']) === 'png') {
            if ($file['type'] === 'image/png') {
                return true;
            }
        }
        return self::ERROR_EXTENSION;
    }

    private static function JPEGChecker($file)
    {
        $pathInfo = pathinfo($file['name']);
        if (strtolower($pathInfo['extension']) === 'jpg') {
            if ($file['type'] === 'image/jpeg') {
                return true;
            }
        }
        return self::ERROR_EXTENSION;
    }

    private static function GIFChecker($file)
    {
        $pathInfo = pathinfo($file['name']);
        if (strtolower($pathInfo['extension']) === 'gif') {
            if ($file['type'] === 'image/gif') {
                return true;
            }
        }
        return self::ERROR_EXTENSION;
    }
}
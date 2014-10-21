<?php

namespace Jason\Http;

/**
 * HTTP 工具
 *
 * @package Jason
 * @author  Jason Wang <jasonwx@163.com>
 */
class Util
{

    /**
     * Strip slashes from string or array
     *
     * This method strips slashes from its input. By default, this method will only
     * strip slashes from its input if magic quotes are enabled. Otherwise, you may
     * override the magic quotes setting with either TRUE or FALSE as the send argument
     * to force this method to strip or not strip slashes from its input.
     *
     * @param  array|string    $rawData
     * @param  bool            $overrideStripSlashes
     * @return array|string
     */
    public static function stripSlashesIfMagicQuotes($rawData, $overrideStripSlashes = null)
    {
        $strip = is_null($overrideStripSlashes) ? get_magic_quotes_gpc() : $overrideStripSlashes;
        if ($strip) {
            return self::stripSlashes($rawData);
        } else {
            return $rawData;
        }
    }

    /**
     * Strip slashes from string or array
     * @param  array|string $rawData
     * @return array|string
     */
    protected static function stripSlashes($rawData)
    {
        return is_array($rawData) ? array_map(array('self', 'stripSlashes'), $rawData) : stripslashes($rawData);
    }

}

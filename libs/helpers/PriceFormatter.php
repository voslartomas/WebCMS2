<?php

namespace WebCMS\Helpers;

/**
 * TODO currencies
 */
class PriceFormatter
{
    /* @var $locale string */
    private static $locale;

    /**
     * Setter for locale.
     * @param string $locale
     */
    public static function setLocale($locale)
    {
        self::$locale = $locale;
    }

    /**
     * Format the given price.
     * @param  type $price
     * @param  type $string
     * @return type
     */
    public static function format($price, $string = "%2n")
    {
        setlocale(LC_MONETARY, self::$locale);

        if (function_exists("money_format")) {
            return money_format($string, $price);
        } else {
            return $price;
        }
    }

}

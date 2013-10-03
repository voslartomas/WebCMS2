<?php 

namespace WebCMS;

/**
 * TODO currencies
 */
class PriceFormatter{
	/* @var $locale string */
	private static $locale;
	
	public static function setLocale($locale){
		
		self::$locale = $locale;
	}
	
	public static function format($price){
		
		setlocale(LC_MONETARY, self::$locale);
		
		$string = "%2n";
		
		if(function_exists("money_format"))
			return money_format($string, $price);
		else
			return $price;
	}
}
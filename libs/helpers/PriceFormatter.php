<?php 

namespace WebCMS;

/**
 * TODO currencies
 */
class PriceFormatter{

	public static function format($price){
		
		setlocale(LC_MONETARY, 'cs_CZ.UTF8');
		
		$string = "%2n";
		
		if(function_exists("money_format"))
			return money_format($string, $price);
		else
			return $price;
	}
}
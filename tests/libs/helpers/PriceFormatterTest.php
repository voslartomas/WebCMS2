<?php

class PriceFormatterTest extends \WebCMS\Tests\EntityTestCase
{
    public function testFormat()
    {
        $locales = \WebCMS\Locales::getSystemLocales();
        \WebCMS\Helpers\PriceFormatter::setLocale($locales[0]);
        $price = \WebCMS\Helpers\PriceFormatter::format(1000);

        $this->assertEquals('€1,000.00', $price);
    }       
}

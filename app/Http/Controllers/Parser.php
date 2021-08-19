<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Parser extends Controller
{

    private static $url = 'https://basicdecor.ru/';

    public static function getProductSiteMap()
    {
        return array(
            'sitemap.products.xml'
        );
    }


    public static function getSiteMapXml()
    {
        $files = self::getProductSiteMap();

        $context = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                )
            )
        );

        foreach ($files as $file) {
            $content = self::getContentXml(self::$url.$file);
            $xml = new \SimpleXMLElement($content);
            \DB::table('product_url')->truncate();
            foreach ( $xml->children() as $item) {
                \DB::table('product_url')->insert( ['Url' => $item->loc] );
            }

        }

    }


    public static function getProductXml()
    {
        $row = \DB::table('product_url')->find(1);
        $content = self::getContentXml($row->Url);
        $product = new DOMDocument();

        //x = $product.getElementsByClassName("product-summary__title")[1];
        var_dump($product);
    }


    private static function  getContentXml($url)
    {
        $context = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                )
            )
        );

        $content = file_get_contents($url, false, $context);

        return $content;
    }
}

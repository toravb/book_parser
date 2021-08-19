<?php
namespace App\Parser\Controllers;

use App\Jobs\ParseImageJob;
use App\Jobs\ParsePageJob;
use App\Models\Parser_item;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Null_;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WXlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as RXlsx;
use function Symfony\Component\String\s;

class SiteMap extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

//    private static $url = 'https://basicdecor.ru/';

    public static function getSiteMap(){
//        $sitemaps = DB::table('options')->where('parameter', '=', 'sitemap')->select('value')->get();

        return array(
            'sitemap.xml'
        );
    }


    public static function getSiteMapXml($id)
    {
        DB::table('sites')->where('id', $id)->update(['doParseSitemap' => 0]);

        $url = DB::table('sites')->where('id', $id)->select('site_url')->first();
        $files = self::getSiteMap();
        foreach ($files as $file) {
            try {
                $contentSiteMaps = self::getContentXml($url->site_url . $file, 'sitemap');
                foreach ($contentSiteMaps['data'] as $item) {
                    try {
                        $sitemap = explode('/', $item)[3];
                        $sitemap = explode('.', $sitemap)[1];
                        if ($sitemap == 'products') {
                            $content = self::getContentXml($item, 'sitemap');

                            DB::table('parsing_status')->updateOrInsert(['site_id' => $id, 'parse_type' => 'sitemap'],
                                ['site_id' => $id, 'parse_type' => 'sitemap', 'Count' => count($content['data']), 'Progress' => 0, 'last_parsing' => null]);

                            foreach ($content['data'] as $uri) {
                                DB::table('product_url')->updateOrInsert(['Url' => $uri, 'site_id' => $id], ['site_id' => $id, 'Url' => $uri,
                                    'doParsePages' => true, 'doParseImages' => true]);
                                DB::table('parsing_status')->where('site_id', $id)->where('parse_type', '=', 'sitemap')
                                    ->increment('Progress', 1);
                            }
                        }

                    }catch (\Exception $e){
                        DB::table('errors')->insert(['site_id' => $id,
                            'Url' => 0,
                            'body' => json_encode($e->getMessage()),
                            'proxy_ip' => 0,
                        ]);
                        continue;
                    }
                }
            }catch (\Exception $e){
                DB::table('errors')->insert(['site_id' => $id,
                    'Url' => $url->site_url,
                    'body' => json_encode($e->getMessage()),
                    'proxy_ip' => 0,
                ]);
                continue;
            }
        }
        try {
            $url = DB::table('parser_items')->where('site_id', $id)->select('Url')->get();

            DB::table('parsing_status')->updateOrInsert(['site_id' => $id, 'parse_type' => 'sitemap'],
                ['site_id' => $id, 'parse_type' => 'sitemap', 'Count' => count($url), 'Progress' => 0]);

            foreach ($url as $uri){

                DB::table('product_url')->updateOrInsert(['Url' => $uri->Url, 'site_id' => $id], ['site_id' => $id, 'Url' => $uri->Url,
                    'doParsePages' => true, 'doParseImages' => true]);
                DB::table('parsing_status')->where('site_id', $id)->where('parse_type', '=', 'sitemap')
                    ->increment('Progress', 1);

            }
            DB::table('product_url')->where('site_id', $id)->where('doParsePages', '=',0)->update(['doParsePages' => true]);
            DB::table('parsing_status')->where('site_id', $id)->where('parse_type', '=', 'sitemap')
                ->update(['last_parsing' => now()]);

        }catch (Exception $e){
            DB::table('errors')->insert(['site_id' => $id,
                'Url' => 0,
                'body' => json_encode($e->getMessage()),
                'proxy_ip' => 0,
            ]);
        }
    }


    public static function getProductImage($site_id, $parse_type = 'image'){

        try {
            $row = DB::table('images')->where('site_id', $site_id)->where('doParse', '=', true)
                ->select()->first();

            $count = DB::table('parsing_status')->where('site_id', $site_id)->where('parse_type', '=', 'image')
                ->select()->first();
            if ($row == null || $count->Progress == $count->Count){
                DB::table('parsing_status')->where('site_id', $site_id)->where('parse_type', '=', 'image')
                    ->update(['last_parsing' => now()]);
            }

        $start = DB::table('sites')->where('id', $site_id)->select()->get()[0];
        if ($start->doParseImages) {

            $url = $row;

            $content = self::getContentXml($url->link, $parse_type, $url->item_id, $site_id);
            if (isset($content['data']['Code'])) {
                if ($content['data']['Code'] == 404) {
                    DB::table('images')->updateOrInsert(['link' => $url->link, 'item_id' => $url->item_id],
                        ['IsDeleted' => 1, 'link' => $url->link, 'doParse' => 0, 'Status' => 0, 'date' => now(), 'proxy_ip' => $content['ip']]);
                } else if (isset($content['data']['Code'])) {
                    $str = str_split($content['data']['Code']);
                    if (($str[0] == 3 || $str[0] == 4) && $content['data']['Code']) {
                        DB::table('images')->where('link', $url->link)->where('item_id', $url->item_id)
                            ->update(['doParse' => 0, 'Status' => 0, 'date' => now(), 'proxy_ip' => $content['ip']]);
                    }
                } else {
                    $str = str_split($content['data']['Code']);
                    if ($str[0] == 5 || $content['data']['Message'] == 'proxy') {
                        sleep(10);
                    }
                }
                DB::table('errors')->insert(['site_id' => $site_id,
                    'Url' => $url->link,
                    'body' => json_encode($content['data']),
                    'proxy_ip' => $content['ip'],
                ]);
            } else if (!isset($content['data']['Error'])) {
                $counters = DB::table('images')->where('site_id', $site_id)->where('doParse', '=', true)
                    ->count();
                DB::table('images')->updateOrInsert(['link' => $url->link, 'item_id' => $url->item_id],
                    ['link' => $url->link, 'doParse' => 0, 'Status' => 1, 'date' => now(), 'proxy_ip' => $content['ip']]);
                DB::table('parsing_status')->where('site_id', $site_id)->where('parse_type', $parse_type)
                    ->increment('Progress', 1, ['Count' => $counters]);
            }


            ParseImageJob::dispatch()->onQueue('doParseImages');

            }
        } catch (\Exception $e) {
            DB::table('errors')->insert(['site_id' => $site_id,
                'Url' => 0,
                'body' => json_encode($e->getMessage()),
                'proxy_ip' => 0,
            ]);
            ParseImageJob::dispatch()->onQueue('doParseImages');
        }
    }

    public static function getProductXml($site_id, $parse_type = 'page')
    {

        try {
            $row = DB::table('product_url')->where('site_id', $site_id)->where('doParsePages', '=',1)
                ->select('Url')->first();
            $count = DB::table('parsing_status')->where('site_id', $site_id)->where('parse_type', '=', 'page')
                ->select()->first();
            if ($row == null || $count->Progress == $count->Count){
                DB::table('parsing_status')->where('site_id', $site_id)->where('parse_type', '=', 'page')
                    ->update(['last_parsing' => now()]);
            }

        $start = DB::table('sites')->where('id', $site_id)->select()->get()[0];
        if ($start->doParsePages && $row != null) {



            $url = $row;

            $content = self::getContentXml($url->Url, $parse_type);

            if (isset($content['data']['Code'])) {
                if ($content['data']['Code'] == 404) {
                    $product = [
                        'Status' => 0,
                        'Last_modified' => now(),
                        'Url' => $content['data']['Url'],
                        'Is_available' => 0,
                        'IsDeleted' => 1,
                        'site_id' => $site_id,
                        'proxy_ip' => $content['ip']
                    ];
                } else if (isset($content['data']['Code'])) {
                    $str = str_split($content['data']['Code']);
                    if (($str[0] == 3 || $str[0] == 4) && $content['data']['Code']) {
                        DB::table('product_url')->where('site_id', $site_id)->where('Url', $content['data']['Url'])->update(['doParsePages' => 0]);
                    }
                } else {
                    $str = str_split($content['data']['Code']);
                    if ($str[0] == 5 || $content['data']['Message'] == 'proxy') {
                        sleep(10);
                    }
                }
                DB::table('errors')->insert(['site_id' => $site_id,
                    'Url' => $url->Url,
                    'body' => json_encode($content['data']),
                    'proxy_ip' => $content['ip'],
                ]);
            } else if (!isset($content['data']['Error'])) {
                $components = self::setElement($content['data']['Components'], $site_id);
                $accessories = self::setElement($content['data']['Accessories'], $site_id);
                $series = self::setElement($content['data']['Series'], $site_id);

                $content['data']['Components'] = $components;
                $content['data']['Accessories'] = $accessories;
                $content['data']['Series'] = $series;

                $product = [
                    'Status' => 1,
                    'Last_modified' => now(),
                    'New' => 1,
                    'Name' => $content['data']['Name'],
                    'Articul' => $content['data']['Articul'],
                    'Url' => $content['data']['Url'],
                    'Is_available' => $content['data']['Is_available'],
                    'Price' => $content['data']['Price'],
                    'Price_action' => $content['data']['Price_action'],
                    'Quantity' => $content['data']['Quantity'],
                    'Series' => $content['data']['Series'],
                    'Components' => $content['data']['Components'],
                    'Accessories' => $content['data']['Accessories'],
                    'Params' => json_encode($content['data']['Params']),
                    'site_id' => $site_id,
                    'proxy_ip' => $content['ip']
                ];
            }

            $item = Parser_item::updateOrCreate(['Url' => $content['data']['Url']], $product);
            $item->save();

            DB::table('product_url')->where('site_id', $site_id)->where('Url', $content['data']['Url'])->update(['doParsePages' => 0]);
            DB::table('parsing_status')->where('site_id', $site_id)->where('parse_type', $parse_type)
                ->increment('Progress', 1);


            $content['data']['New'] = 1;


            if ($item->ID != null) {
                $id = $item->ID;
            } else {
                $id = $item->id;
            }
            if (!$item->wasRecentlyCreated) {
                DB::table('parser_items')->where('ID', $id)->update(['New' => 0]);
                $content['data']['New'] = 0;
            }

            if (isset($content['data']['Images'])) {
                $imagesLinks = $content['data']['Images'];
                unset($content['data']['Images']);
                $images = DB::table('images')->where('item_id', $id)->select('link')->get();
                $insertImage = [];
                if ($images != null) {
                    $dbImages = [];
                    foreach ($images as $image) {
                        $dbImages[] = $image->link;
                    }
                    $links = array_diff($imagesLinks, $dbImages);
                    if ($links != null) {
                        foreach ($links as $link) {
                            $insertImage[] = [
                                'item_id' => $id,
                                'link' => $link,
                                'site_id' => $site_id,
                            ];
                        }
                        DB::table('images')->insert($insertImage);
                    }
                } else {
                    foreach ($imagesLinks as $link) {
                        $insertImage[] = [
                            'item_id' => $id,
                            'link' => $link,
                            'site_id' => $site_id,
                        ];
                    }
                    DB::table('images')->insert($insertImage);
                }
            }
//            ExcelController::writeExcel($content['data'], $id);
//                (new ParsePageJob())->release();
            ParsePageJob::dispatch()->onQueue('doParsePages');
            }
        } catch (\Exception $e) {
            DB::table('errors')->insert(
                ['site_id' => $site_id,
                    'Url' => 0,
                    'body' => json_encode($e->getMessage()),
                    'proxy_ip' => 0,
                ]);
//                (new ParsePageJob())->release();
            ParsePageJob::dispatch()->onQueue('doParsePages');
        }
    }

    private static function setElement($array, $site_id){
        $elements = '';
        try {

            foreach ($array as $element) {
                if ($element === 0) {
                    $elements .= 0;
                    return $elements;
                }
                try {
                    $insertElement = [
                        'Status' => 0,
                        'Last_modified' => now(),
                        'New' => 0,
                        'Name' => 0,
                        'Articul' => 0,
                        'Url' => $element,
                        'Is_available' => 0,
                        'Price' => 0,
                        'Price_action' => 0,
                        'Quantity' => 0,
                        'Series' => 0,
                        'Components' => 0,
                        'Accessories' => 0,
                        'Params' => json_encode([0]),
                        'site_id' => $site_id,
                        'proxy_ip' => 0
                    ];
                    $el = Parser_item::firstOrNew(['Url' => $element, 'site_id' => $site_id]);
                    if ($el->exists){
                        $id = $el->ID;
                    }else{
                        $el->fill($insertElement);
                        $el->save();
                        if ($el->ID != null) {
                            $id = $el->ID;
                        } else {
                            $id = $el->id;
                        }
                    }

                    $elements .= "$id,";
                } catch (\Exception $e) {
                    DB::table('errors')->insert(
                        ['site_id' => $site_id,
                            'Url' => 0,
                            'body' => json_encode($e->getMessage()),
                            'proxy_ip' => 0,
                        ]);
                }
            }
        }catch (\Exception $e){
            DB::table('errors')->insert(
                ['site_id' => $site_id,
                    'Url' => 0,
                    'body' => json_encode($e->getMessage()),
                    'proxy_ip' => 0,
                ]);
        }
        return $elements;
    }


    public static function  getContentXml($url, $proxy_ip, $item_id = 0, $site_id = 1)
    {
        set_time_limit(60*60*24);

//        $proxy_ip = self::getProxy();
//        $proxy_ip = 0;
        $command = escapeshellcmd("python ".app_path('Parser/Controllers'). "/parse.py ". $url .
            " " . $proxy_ip . " " . $item_id . " " . $site_id);
        $output = shell_exec($command);
//        $data = json_decode($output, true);

        return $output;
//        if (isset($data['Message']) && $data['Message'] == 'proxy'){
//            DB::table('proxies')->where('proxy', '=', $proxy_ip)->update(['blocked' => 1, 'update_time' => now()]);
//        }
//
//        return [
//            'data' => $data,
//            'ip' => $proxy_ip,
//            ];
    }

    private static function getProxy(){

        return DB::table('proxies')->where('blocked', '!=', '1')->inRandomOrder()->limit(1)->get('proxy')[0]->proxy;
    }




}

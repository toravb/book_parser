<?php

namespace App\Http\Controllers\Parser\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DownloadExcelJob;
use App\Jobs\ParseImageJob;
use App\Jobs\ParsePageJob;
use App\Jobs\ParseSitemapJob;
use App\Parser\Controllers\ExcelController;
use App\Parser\Controllers\ProxyAggregator;
use Illuminate\Http\Request;
use App\Parser\Controllers\SiteMap;
use Illuminate\Support\Facades\DB;

class ParserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sites = DB::table('sites')->select()->get();
        $data = null;
        $parsingStatus = null;
        foreach ($sites as $site){
            if ($site->site == $request->site){
                $data = $site;
                $pagesStatus = DB::table('parsing_status')->where('site_id', $data->id)->select()->get();
                $statusPages = DB::table('jobs')->where('queue', '=', 'doParsePages')->count();
                $statusImages= DB::table('jobs')->where('queue', '=', 'doParseImages')->count();
                $statuses = [
                    'page' => $statusPages,
                    'image' => $statusImages,
                ];
                $count =[
                    'countPages' => DB::table('product_url')->where('site_id', $data->id)->count(),
                    'toParsePages' => DB::table('product_url')->where('site_id', $data->id)->where('doParsePages', true)->count(),
                    'countImages' => DB::table('images')->where('site_id', $data->id)->count(),
                    'toParseImages' => DB::table('images')->where('site_id', $data->id)->where('doParse', true)->count(),
                ];
            }
        }
        return view('pages.parser.parser',['site' => $data, 'parsingStatus' => $pagesStatus, 'counts' => $count, 'statuses' => $statuses] , compact('sites', $sites));
    }

    public function parsePage(Request $request){

        DB::table('sites')->where('site', $request->site)->update(['doParsePages' => !$request->doParsePages]);
        //parser
//        if ($request->doParsePages == false) {
//            $count = DB::table('product_url')->where('site_id', $request->id)->where('doParsePages', '=', 1)->count();
//
//            DB::table('parsing_status')->updateOrInsert(['site_id' => $request->id, 'parse_type' => 'page'],
//                ['Count' => $count, 'Progress' => 0, 'last_parsing' => null]);
//        }
//        if ($request->doParsePages == true) {
//            DB::table('parsing_status')->updateOrInsert(['site_id' => $request->id, 'parse_type' => 'page'],['last_parsing' => now()]);
//        }
        //books

        $book = DB::table('books')->find(1);
        if ($book != null){
            $a = SiteMap::getContentXml($book->url, 0);
            $links = explode(',', $a);

            for ($i = 0; $i < count($links) - 1; $i++){
                $page = SiteMap::getContentXml($links[$i], 1);
                DB::table('books_pages')->insert([
                    'book_id' => $book->id,
                    'book_page' => $i,
                    'content' => $page
                ]);
            }
        }


        return back();
    }

    public function parsePageImage(Request $request){
        DB::table('sites')->where('site', $request->site)->update(['doParseImages' => !$request->doParseImages]);
        if ($request->doParseImages == false) {
            $count = DB::table('images')->where('site_id', $request->id)->where('doParse', '=', 1)->count();

            DB::table('parsing_status')->updateOrInsert(['site_id' => $request->id, 'parse_type' => 'image'],
                ['Count' => $count, 'Progress' => 0, 'last_parsing' => null]);
        }
        if ($request->doParseImages == true) {

            DB::table('parsing_status')->updateOrInsert(['site_id' => $request->id, 'parse_type' => 'image'],['last_parsing' => now()]);
        }


        return back();
    }
    public function parseProxy(){
        $proxy = new ProxyAggregator;
        $mess = $proxy->saveProxy();
        return back()->with('success',$mess['message']);
    }

    public function addPagesToQueue(Request $request){
        $sites = DB::table('sites')->select()->get();

        foreach ($sites as $site){
            if ($site->site == $request->site){
                $data = $site;
                $page = DB::table('product_url')->where('site_id', $data->id)->where('doParsePages', false)->update(['doParsePages' => true]);

                $count = DB::table('product_url')->where('site_id', $data->id)->where('doParsePages', '=', 1)->count();
                DB::table('parsing_status')->updateOrInsert(['site_id' => $data->id, 'parse_type' => 'page'],
                    ['Count' => $count, 'Progress' => 0, 'last_parsing' => null]);
//                $image = DB::table('images')->where('site_id', $data->id)->where('doParse', false)->update(['doParse' => true]);

                return back()->with('success', "В очередь было добавлено: $page страниц");// и $image изображений");
            }
        }
    }

    public function parseSiteMap(Request $request){
        DB::table('sites')->where('id', $request->site_id)->update(['doParseSitemap' => true]);

        return back()->with('success', 'Сборка sitemap запущена');
    }

    public static function getDownload(Request $request)
    {
        $file= storage_path('parser'). "/parser.xlsx";
        if (file_exists($file)) {

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];
//            DB::table('sites')->where('id', $request->id)->update(['downloadedExcel' => true]);
            return response()->download($file, 'parser.xlsx', $headers);
        }
        return back()->with('error', 'Файла не существует');
    }

    public static function generateExcel(Request $request)
    {
        DB::table('sites')->where('id', $request->id)->update(['downloadedExcel' => true]);
        return back();
    }
}

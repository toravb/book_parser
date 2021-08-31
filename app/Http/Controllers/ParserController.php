<?php

namespace App\Http\Controllers;

use App\Jobs\ParseBookJob;
use App\Jobs\ParsePageJob;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookLink;
use App\Models\Publisher;
use App\Models\Series;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function Sodium\increment;

class ParserController extends Controller
{

    protected static $books_uri = 'http://loveread.ec/letter_nav.php?let=';

    public static function parseLinks()
    {
        DB::table('sites')->where('site', '=', 'loveread.ec')->update(['doParseLinks'=>false]);
        $links = self::startParsing(self::$books_uri, 'links')['data'];
        DB::table('parsing_status')->where('parse_type', '=', 'links')->update(['Count' => count($links)]);
        $i = 1;
        foreach ($links as $link){
            BookLink::firstOrCreate($link);
            DB::table('parsing_status')->where('parse_type', '=', 'links')->update(['Progress' => $i]);
            $i++;
        }
    }

    public static function parseBooks()
    {
        $link = BookLink::where('doParse', true)->first();
        $data = self::startParsing($link->link, 'book')['data'];

        print_r($data);
//        exit();
//
//        $book = $data['book']['params'];
//        $book['author_id'] = (isset($data['book']['search']['author']))?Author::firstOrCreate(['author' =>$data['book']['search']['author']])->id:null;
//        $book['series_id'] = (isset($data['book']['search']['series']))?Series::firstOrCreate(['series' =>$data['book']['search']['series']])->id:null;
//        $book['publisher_id'] = (isset($data['book']['search']['publisher']))?Publisher::firstOrCreate(['publisher' =>$data['book']['search']['publisher']])->id:null;
//        $book['year_id'] = (isset($data['book']['search']['year']))?Year::firstOrCreate(['year' =>$data['book']['search']['year']])->id:null;
//        $book['params'] = json_encode($book['params']);
//        $book['link'] = $link->link;
//
//        $created_book = Book::firstOrCreate($book);
//        $created_book->image()->create($data['image']);
//        $created_book->pageLinks()->createMany($data['pages']);
//
        $link->update(['doParse' => false]);
//        DB::table('parsing_status')->where('parse_type', '=', 'books')->increment('Progress');
//
        ParseBookJob::dispatch()->onQueue('doParseBooks');
    }

    public static function  startParsing($url, $type)
    {
        set_time_limit(60*60*24);

//        $proxy_ip = self::getProxy();
        $proxy_ip = 0;
        $command = escapeshellcmd("python ".app_path('Parser/Controllers'). "/parse.py ". $url .
            " " . $proxy_ip . " " . $type);
        $output = shell_exec($command);
        $data = json_decode($output, true);

        if (isset($data['Message']) && $data['Message'] == 'proxy'){
            DB::table('proxies')->where('proxy', '=', $proxy_ip)->update(['blocked' => 1, 'update_time' => now()]);
        }

        return [
            'data' => $data,
            'ip' => $proxy_ip,
        ];
    }

    private static function getProxy(){

        return DB::table('proxies')->where('blocked', '!=', '1')->inRandomOrder()->limit(1)->first('proxy')->proxy;
    }
}

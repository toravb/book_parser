<?php

namespace App\Http\Controllers\Parser\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parser_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
//        $items = Parser_item::all();
//        return view('pages.parser.page', ['items' => $items]);
        $sites = DB::table('sites')->select('site')->get();
        $pages = DB::table('parser_items')->where('Articul', '!=', 0)->paginate(100);
        return view('pages.parser.page', ['items' => $pages], compact('sites', $sites));
    }

    public function showProxies(){
        $sites = DB::table('sites')->select('site')->get();
        $proxies = DB::table('proxies')->paginate(100);
        return view('pages.parser.showproxy', ['items' => $proxies], compact('sites', $sites));
    }
}

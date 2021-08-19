<?php

namespace App\Parser\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProxyAggregatorCannotGetProxyList extends \Exception { };
class SaveProxyException extends \Exception {}

class ProxyAggregator {

    private $fineProxyLogin;
    private $fineProxyPass;
    private $proxies = [];
    private $currentProxyNumber = 0;

    function __construct() {

        $this->fineProxyLogin = DB::table('options')->where('parameter', 'login')->first();
        $this->fineProxyPass  = DB::table('options')->where('parameter', 'password')->first();

        if ($this->fineProxyLogin != null && $this->fineProxyPass != null) {
            $apiUrl = "http://account.fineproxy.org/api/getproxy/?format=txt&type=httpip&login={$this->fineProxyLogin->value}&password={$this->fineProxyPass->value}";
        }else{
            throw new ProxyAggregatorCannotGetProxyList('cannot get proxies by api: login or pass is null');
        }

        try {
            $proxyTxt = trim( file_get_contents($apiUrl) );

        } catch (\Exception $e) {
            throw new ProxyAggregatorCannotGetProxyList('cannot get proxies by api: ' . $proxyTxt . $e->getMessage());
        }
        if (!$proxyTxt || $proxyTxt=='AUTH ERROR') {
            throw new ProxyAggregatorCannotGetProxyList('cannot get proxies by api: ' . $proxyTxt);
        }
        $strproxies = explode("\n", $proxyTxt);


        foreach ($strproxies as $v) {
            $this->proxies[] = $v;
        }

    }

    public function getNextProxy() {

        $res = each($this->proxies);
        if ($res===false) {
            reset($this->proxies);
            $res = each($this->proxies);
        }

        return $res['key'];

    }

    public function removeProxy($ip) {

        unset($this->proxies[$ip]);

    }

    public function saveProxy() {
        $count = count($this->proxies);
        DB::table('proxies')->truncate();
        $i = 0;
        foreach ($this->proxies as $proxy){
            $f_proxy = trim(preg_replace('/\s\s+/', ' ', $proxy));
            try {
                DB::table('proxies')->updateOrInsert( ['proxy' => $f_proxy], ['proxy' => $f_proxy] );
                $i++;
            }catch (\Exception $e){
                DB::table('errors')->insert(
                    ['site_id' => 0,
                        'Url' => 0,
                        'body' => json_encode($e->getMessage()),
                        'proxy_ip' => $f_proxy,
                    ]);
            }
        }
        return $mess = [
            'message' => "Спаршено $i прокси из $count"
        ];
    }

}



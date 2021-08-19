<?php

namespace My;

class ProxyAggregatorCannotGetProxyList extends \Exception { };
class SaveProxyException extends \Exception {}

class ProxyAggregator {

    private $fineProxyLogin = 'sales@integrator.digital';
    private $fineProxyPass = 'MCjZPcaOJr';
    private $proxies = [];
    private $currentProxyNumber = 0;

    function __construct() {

        $this->fineProxyLogin = Param::getParam('fineproxy_login');
        $this->fineProxyPass  = Param::getParam('fineproxy_pass');

//        $apiUrl = "http://account.fineproxy.org/api/getproxy/?format=txt&type=httpip&login={$this->fineProxyLogin}&password={$this->fineProxyPass}";
        $apiUrl = "http://account.fineproxy.org/api/getproxy/?format=txt&type=httpip&login=MiniRUS413166&password=OkBe5KBUuh";
        try {
            $proxyTxt = trim( file_get_contents($apiUrl) );
        } catch (\Exception $e) {
            throw new ProxyAggregatorCannotGetProxyList('cannot get proxies by api: ' . $proxyTxt . $e->getMessage());
        }
        if (!$proxyTxt || $proxyTxt=='AUTH ERROR') {
            throw new ProxyAggregatorCannotGetProxyList('cannot get proxies by api: ' . $proxyTxt);
        }
        //var_dump($proxyTxt);
        //die('ddd');
        $strproxies = explode("\n", $proxyTxt);

        shuffle($strproxies);

        foreach ($strproxies as $v) {
            $this->proxies[ trim($v) ] = true;
        }

        //print_r($this->proxies);

    }

    public function getNextProxy() {

        //return
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
//        $f = fopen ($_SERVER['DOCUMENT_ROOT']."/errors_".date('d-m-Y').".log", "a");
        foreach ($this->proxies as $proxy){
//            try {
                \DB::table('options')->insert( ['proxy' => $proxy] );
//            }catch (\Exception $e){
//                fwrite ($f, "\n".$proxy.';');
//                throw new SaveProxyException('Save error. IP: ' . $proxy . $e->getMessage());
//            }
        }
//        fclose($f);
    }

}



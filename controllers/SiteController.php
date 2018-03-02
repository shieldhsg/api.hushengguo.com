<?php

namespace app\controllers;

use app\components\BaseController;
use app\helpers\CurlHelper;

class SiteController extends BaseController
{
    /**
     * 跳转到接口文档
     */
    function actionIndex()
    {
        $eqid = '9ed09f0d0000c22d000000045a964bce';
        $host = 'referer.bj.baidubce.com';
        $uri =  '/v1/eqid/'.$eqid;
        $signString = $this->sign(($uri));
        $headers[] = 'GET '.$uri.'HTTP/1.1';
        $headers[] = 'accept-encoding: gzip, deflate';
        $headers[] = 'x-bce-date: '.$this->getDate();
        $headers[] = 'host: referer.bj.baidubce.com';
        $headers[] = 'accept: /';
        $headers[] = 'connection: keep-alive';
        $headers[] = 'contenttype: application/json';
        $headers[] = 'authorization: '.$signString.'';
        $res = CurlHelper::get('http://'.$host.$uri,$headers);
        var_dump($res);die;
        //return $this->redirect('swagger-ui/dist/index.html');
    }

    function sign($uri)
    {
        $secretKey = 'dcd0abfb01f34442b4293c4254937d25';
        $accessKey = '8136a22f945b44a1b4ed333bb214c1ad';
        $expireTime = 1800;
        $preFix = 'bce-auth-v1/'.$accessKey.'/'.$this->getDate().'/'.$expireTime;
        $http_method = "GET";
        $canonicalURI = $this->uriEncode($uri,false);
        $canonicalQueryString = '';
        $canonicalHeaders = 'host:bj.bcebos.com';
        $canonicalRequest = $http_method."\n".$canonicalURI."\n".$canonicalQueryString. "\n".$canonicalHeaders;
        var_dump($canonicalRequest);die;
        $signKey = strtolower(bin2hex(hash_hmac('sha256', $preFix, $secretKey, true)));
        $signature = strtolower(bin2hex(hash_hmac('sha256', $signKey, $canonicalRequest, true)));
        $signString = 'bce-auth-v1/'.$accessKey.'/'.$this->getDate().'/'.$expireTime.'/host/'.$signature;
        return $signString;
    }

    function uriEncode($input,$encodeSlash)
    {
        $str = '';
        for($i=0;$i<strlen($input);$i++){
            $ch = substr($input,$i,1);
            if (($ch >= 'A' && $ch <= 'Z') || ($ch >= 'a' && $ch <= 'z') || ($ch >= '0' && $ch <= '9') || $ch == '_' || $ch == '-' || $ch == '~' || $ch == '.') {
                $str .= $ch;
            }else if ($ch == '/') {
                $str.=$encodeSlash ? "%2F" : $ch;
            } else {
                $str .= $this->hexEncode($ch);
            }
        }
        return $str;
    }

    function hexEncode($s) {
        return rawurlencode($s);
    }

    function getDate()
    {
        return date('Y-m-d').'T'.date('H:i:s').'Z';
    }


}
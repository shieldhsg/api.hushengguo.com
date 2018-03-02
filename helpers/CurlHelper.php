<?php
namespace app\helpers;

class CurlHelper
{

    public static  function get($url,$header)
    {
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL,$url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, false);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //var_dump($header);die;
        //显示获得的数据
        return $data;
    }

    public static  function post()
    {

    }

}

?>
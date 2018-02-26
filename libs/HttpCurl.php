<?php

namespace app\libs;

use yii\helpers\Json;

class HttpCurl
{
    private $handle = null;
    //请求结果参数
    public $httpInfo;
    public $httpCode;
    public $httpMsg;

    public function __construct()
    {
        //初始化
        $this->handle = curl_init();
        //初始化参数
        $this->initOptions();
    }

    //关闭
    public function closeHandle()
    {
        curl_close($this->handle);
    }

    /**
     * 设置单个参数
     * @param $option
     * @param $value
     * @return bool
     */
    public function setOption($option, $value)
    {
        return curl_setopt($this->handle, $option, $value);
    }

    /**
     * 设置多个参数
     * @param $options
     * @return bool
     */
    public function setOptions($options)
    {
        return curl_setopt_array($this->handle, $options);
    }

    /**
     * 设置超时时间
     * @param $timeout
     */
    public function setTimeout($timeout)
    {
        $this->setOption(CURLOPT_TIMEOUT, $timeout);
    }

    /**
     * 设置连接时间
     * @param $timeout
     */
    public function setConnectTimeout($timeout)
    {
        $this->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
    }

    /**
     * 发送请求
     * @param array $customHeader
     * @return Response
     */
    protected function send(array $customHeader = [])
    {
        $header = [];
        if (!empty($customHeader)) {
            $header = $customHeader;
        }
        $header = array_unique($header, SORT_STRING);
        $this->setOption(CURLOPT_HTTPHEADER, $this->headerOption($header));
        $content = curl_exec($this->handle);
        //获取请求头
        $this->httpInfo = curl_getinfo($this->handle);
        //获取状态码
        $httpCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
        $this->httpCode = $httpCode;
        //验证请求结果
        if ($errno = curl_errno($this->handle)) {
            $this->httpMsg = curl_error($this->handle);
            return '';
        }
        $this->httpMsg = '请求成功!';
        return $content;
    }

    /**
     * 请求头参数转换
     * @param $header
     * @return array
     */
    protected function headerOption($header)
    {
        $data = [];
        if(!empty($header)){
            foreach ($header as $k => $v)
            {
                $data[] = $k.': '.$v;
            }
        }
        return $data;
    }

    /**
     * 初始化post参数值
     * @param $params
     * @param bool $isJson
     * @param bool $useEncoding
     */
    protected function initPostFields($params, $isJson = false, $useEncoding = false)
    {
        if($useEncoding){
            if (is_array($params)) {
                $useEncodingBool = true;
                foreach ($params as $param) {
                    if (is_string($param) && preg_match('/^@/', $param)) {
                        $useEncodingBool = false;
                        break;
                    }
                }
                if ($useEncodingBool) {
                    $params = http_build_query($params);
                }
            }
        }
        if (!empty($params)) {
            $this->setOption(CURLOPT_POSTFIELDS, !$isJson ? $params : Json::encode($params));
        }
    }

    /**
     * 请求
     * @param $url
     * @param array $data
     * @param int $method 0 get 1 post
     * @param int $type 0 普通 1 json
     * @return array|mixed
     */
    public function request($url, $data = array(), $method = 0, $type = 1)
    {
        if($method = 0){
            //get
            $result =$this->get($url);
        } else {
            //post
            $result = $this->post($url, $data);
        }
        if($type != 0){
            if(empty($result))return array();
            try{
                $data = Json::decode($result, true);
            }catch (\Exception $exception){
                $data = [];
            }
            return $data;
        }
        return $result;
    }

    /**
     * GET请求
     * @param string $uri
     * @param array $params
     * @param array $customHeader
     * @return Response
     */
    public function get($uri, $customHeader = [])
    {
        $this->setOptions([
            CURLOPT_URL           => $uri,
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);
        return $this->send($customHeader);
    }

    /**
     * POST请求
     * @param $uri
     * @param array $params
     * @param bool $useEncoding
     * @param array $customHeader
     * @return Response
     */
    public function post($uri, $params = [], $customHeader = [], $isJson = false, $useEncoding = false)
    {
        $this->setOptions([
            CURLOPT_URL           => $uri,
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ]);
        $this->initPostFields($params, $isJson, $useEncoding);
        return $this->send($customHeader);
    }

    //初始化参数
    private function initOptions()
    {
        $this->setOptions([
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 20,
            CURLOPT_HEADER          => false,
            CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT       => '',
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30,
        ]);
    }

}
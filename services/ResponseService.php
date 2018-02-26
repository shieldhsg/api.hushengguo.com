<?php

namespace app\services;

use Yii;


class ResponseService extends BaseService
{
    /**
     * 成功
     * @param $data
     * @param string $msg
     * @return mixed
     */
    public static function success($data, $msg = '', $code = 200)
    {
        return self::handle($data, $code, $msg);
    }

    /**
     * 失败
     * @param $code
     * @param array $data
     * @param string $msg
     * @return mixed
     */
    public static function error($code, $msg = '', $data = [])
    {
        return self::handle($data, $code, $msg);
    }

    /**
     * 处理
     * @param $data
     * @param int $code
     * @param string $msg
     * @return mixed
     */
    public static function handle($data, $code = 200, $msg = '')
    {
        Yii::$app->response->statusCode = $code;
        Yii::$app->response->statusText = $msg;
        return $data;
    }
}
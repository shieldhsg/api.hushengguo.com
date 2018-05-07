<?php
/**
 * Restful api 接口模块
 */
namespace app\modules\api;

use Codeception\Events;
use Yii;
use yii\base\Event;
use yii\db\BaseActiveRecord;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\api\controllers';

    public function init()
    {
        parent::init();
        header("Access-Control-Allow-Origin: *");
        //针对restful api 模块定制配置
        \Yii::configure(\Yii::$app, [
            'components' => [
                'request' => [
                    'class' => '\yii\web\Request',
                    'enableCookieValidation' => false,
                    'parsers' => [
                        'application/json' => 'yii\web\JsonParser',
                    ],
                ],
                'response' => [
                    'class' => 'yii\web\Response',
                    'on beforeSend' => function ($event) {
                        //restful api
                        $response = $event->sender;
                        $code = $response->getStatusCode();
                        $msg = $response->statusText;
                        if ($code == 404) {
                            !empty($response->data['message']) && $msg = $response->data['message'];
                        }
                        //返回数据格式
                        $data = [
                            'code' => $code,
                            'msg' => $msg,
                            'data' => $response->data
                        ];
                        $response->data = $data;
                        $response->format = yii\web\Response::FORMAT_JSON;
                        //设置固定响应状态值
                        $response->statusCode = 200;
                    },
                ],
            ],
            'on afterRequest' => function($event){
                //restful api
                //获取响应结果
                $response = $event->sender;
                if($response->response->statusCode == 200 && Yii::$app->controller->logAutomatic){
                    //自动收集操作日志
                    Yii::$app->controller->log();
                }
            }
        ]);
        \Yii::$app->user->enableSession = false;
        \Yii::$app->user->loginUrl = null;
    }
}
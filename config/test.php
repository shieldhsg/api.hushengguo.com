<?php
$params = require(__DIR__ . '/dev/params.php');
$db = require(__DIR__ . '/dev/db.php');
$redis = require(__DIR__ . '/dev/redis.php');

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'Asia/Shanghai',
    'language' => 'zh-cn',
    'charset' => 'utf-8',
    'components' => [
        'request' => [
        ],

        //数据库配置
        'db' => $db,
        //邮件
        'mailer' => [

        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' =>true,
            //路由规则配置
            'rules' => require(__DIR__ . '/url-rules.php')
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'idParam' => '__user',
//            'enableSession' => false,
//            'loginUrl' => null
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@app/runtime/logs/dev.log',
                ]
            ],
        ],
    ],
    //模块配置
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
],
    'params' => $params,
];

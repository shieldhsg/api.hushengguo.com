<?php

//基础路由
$baseRuleConfigs = [
    /* 基础 */
    '/' => 'site/index',
];

//api接口路由
$apiRuleConfigs = [
    [
        'controller' => ['api/article'],
        'patterns' => [
            //获取信息
            'GET' => 'get'
        ],
    ],
    //上传接口
    [
        'controller' => ['api/upload'],
        'patterns' => [
            //上传文件
            'POST file' => 'file',
            'GET download'=>'download'
        ]
    ],
    //下载接口
    [
        'controller' => ['api/download'],
        'patterns' => [
            //上传文件
            'GET' => 'index',
        ]
    ],

];

/**
 * 基础的api url规则配置
 */
$apiUrls = array_map(function($unit)
{
    $urlRule = $unit;
    //防止默认options控制器被屏蔽
    if(isset($unit['only'])&&!empty($unit['only'])&&!in_array('options', $unit['only'])){
        $urlRule['only'][] = 'options';
    }
    if(isset($unit['except'])&&!empty($unit['except'])&&in_array('options', $unit['except'])){
        $urlRule['except'] = array_merge(array_diff($unit['except'], ['options']));
    }
    //由于ajax设置请求头后,会有一次options请求,默认为所有路由添加支持options请求
    if(isset($unit['extraPatterns'])&&!empty($unit['extraPatterns'])){
        foreach ($unit['extraPatterns'] as $key => $val)
        {
            if(!is_numeric(strpos($key, 'OPTIONS'))){
                //判断是否有空格符
                if(is_numeric(strpos($key, ' '))){
                    //存在
                    $tmp = explode(' ', $key);
                    $k = str_replace($tmp[0], 'OPTIONS', $key);
                    $urlRule['extraPatterns'][$k] = 'options';
                } else {
                    //不存在
                    $urlRule['extraPatterns']['OPTIONS'] = 'options';
                }
            }
        }
    }
    if(isset($unit['patterns'])&&!empty($unit['patterns'])) {
        foreach ($unit['patterns'] as $key => $val) {
            if (!is_numeric(strpos($key, 'OPTIONS'))) {
                //判断是否有空格符
                if (is_numeric(strpos($key, ' '))) {
                    //存在
                    $tmp = explode(' ', $key);
                    $k = str_replace($tmp[0], 'OPTIONS', $key);
                    $urlRule['patterns'][$k] = 'options';
                } else {
                    //不存在
                    $urlRule['patterns']['OPTIONS'] = 'options';
                }
            }
        }
    }
    $config = [
        'class' => 'yii\rest\UrlRule',
        'pluralize' => false,
        //默认
        'tokens' => [
            '{id}' => '<id:\\d[\\d,]*>',
            '{projectId}' => '<projectId:\\d+>',
            '{accidentId}' => '<accidentId:\\d+>',
            '{versionId}' => '<versionId:\\d+>',
            '{scheduleId}'=> '<scheduleId:\\d+>',
            '{typeId}'=> '<typeId:\\d+>',
            '{standardId}'=> '<standardId:\\d+>',
            '{info_id}'=> '<info_id:\\d+>',
            '{item_id}'=> '<item_id:\\d+>',
            '{material_id}'=> '<material_id:\\d+>',
            '{dataId}'=> '<dataId:\\d+>',
            '{logId}'=> '<logId:\\d+>',
            '{rater_id}'=>'<rater_id:\\d+>',
            '{standard_id}'=>'<standard_id:\\d+>'
        ]
    ];
    return array_merge($config, $urlRule);
}, $apiRuleConfigs);
//合并整个项目路由
return array_merge($baseRuleConfigs, $apiUrls);
<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
require __DIR__ . '/../../hsg.vendor/vendor/autoload.php';
require __DIR__ . '/../../hsg.vendor/vendor/yiisoft/yii2/Yii.php';

//文章类相关model
//require __DIR__ . '/../../model/models/Articles.php';
//require __DIR__ . '/../../model/models/query/ArticlesQuery.php';
//require __DIR__ . '/../../model/models/search/ArticlesSearch.php';

$config = require __DIR__ . '/../config/test.php';

(new yii\web\Application($config))->run();

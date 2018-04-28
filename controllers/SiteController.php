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
        return $this->redirect('swagger-ui/dist/index.html');
    }
    

}
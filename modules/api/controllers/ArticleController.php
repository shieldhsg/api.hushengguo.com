<?php
/**
 * 文章列表管理
 */

namespace app\modules\api\controllers;

use app\components\ApiController;
use app\helpers\ResponseHelper;
use app\models\search\ArticlesSearch;

class ArticleController extends  ApiController
{

    public function actionGet()
    {
        $articles  = (new ArticlesSearch())->search([]);
        return $this->responseService->success($articles,ResponseHelper::SUCCESS_MSG);
    }

}
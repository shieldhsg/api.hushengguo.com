<?php
/**
 * 文章列表管理
 */

namespace app\modules\api\controllers;

use app\components\ApiController;
use app\helpers\ResponseHelper;
use app\models\Articles;
use app\models\search\ArticlesSearch;

class ArticleController extends  ApiController
{

    public function actionGet()
    {
        $moduleId = $this->getInt('module_id');
        if(!$moduleId){
            $moduleId = null;
        }
        $articles = Articles::find()
            ->select(['id','name','abstract','create_time'])
            ->filterWhere(['status'=>1,'module_id'=>$moduleId])
            ->all();
        return $this->responseService->success($articles,ResponseHelper::SUCCESS_MSG);
    }

    public function actionGetDetail()
    {
        $articleId = $this->getInt('article_id');
        $article = Articles::findOne([
            'status'=>1,
            'id'=>$articleId
        ]);
        $res['title'] = $article['name'];
        $res['content'] = $article['content'];
        return $this->responseService->success($res,ResponseHelper::SUCCESS_MSG);
    }

}
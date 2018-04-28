<?php
/**
 * 碎片管理
 */

namespace app\modules\api\controllers;

use app\components\ApiController;
use app\helpers\ResponseHelper;
use app\models\Fragment;

class FragmentController extends  ApiController
{

    public function actionGet()
    {
        $moduleId = $this->getInt('module_id');
        if(!$moduleId){
            $moduleId = null;
        }
        $articles = Fragment::find()
            ->select(['id','name','content','create_time'])
            ->filterWhere(['status'=>1,'module_id'=>$moduleId])
            ->all();
        return $this->responseService->success($articles,ResponseHelper::SUCCESS_MSG);
    }

}
<?php
/**
 * 文章列表管理
 */

namespace app\modules\api\controllers;

use app\components\ApiController;
use app\helpers\ResponseHelper;
use app\models\Images;
use app\models\UploadFile;
use yii\web\UploadedFile;

class ImageController extends  ApiController
{

    public function actionGet()
    {
        $moduleId = $this->getInt('module_id');
        if(!$moduleId){
            $moduleId = null;
        }
        $images = Images::find()->alias('i')
            ->select(['i.id','i.name','f.filename as path','i.file_id','i.url','i.create_time'])
            ->leftJoin(UploadFile::tableName().' as f','f.id = i.file_id')
            ->filterWhere(['status'=>1,'module_id'=>$moduleId])
            ->asArray()
            ->all();
        foreach ($images as $k=>&$v){
            $v['path'] = str_replace("../../",\Yii::$app->params['host'],$v['path']);
        }
        return $this->responseService->success($images,ResponseHelper::SUCCESS_MSG);
    }


}
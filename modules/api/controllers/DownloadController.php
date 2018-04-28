<?php
/**
 * 下载模块
 * Created by yum
 * Email: yum@uuzu.com
 * Date: 2017/9/4
 * Time: 16:04
 */
namespace app\modules\api\controllers;

use app\helpers\ResponseHelper;
use app\models\UploadFile;
use Yii;
use app\components\ApiController;

class DownloadController extends ApiController
{
    public $except = ['index'];

    /**
     * 下载文件
     * @return $this
     */
    public function actionIndex()
    {
        $fileId = $this->getInt('file_id');
        $model = UploadFile::findOne([
            'id'=>$fileId
        ]);
        if(empty($model)){
            return $this->responseService->error(ResponseHelper::DATA_NOT_FOUND,ResponseHelper::DATA_NOT_FOUND_MSG);
        }
        return Yii::$app->response->sendFile($model->filename,$model->name);
    }
}
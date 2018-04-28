<?php
/**
 * 上传接口
 * Created by yum
 * Email: yum@uuzu.com
 * Date: 2017/8/28
 * Time: 9:28
 */
namespace app\modules\api\controllers;

use app\libs\Helper;
use app\models\UploadFile;
use Yii;
use app\components\ApiController;
use yii\web\UploadedFile;

class UploadController extends ApiController
{
    public $except = ['file'];

    /**
     * 上传文件
     */
    public function actionFile()
    {
        $model = new UploadFile();
        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {
                //上传目录
                $dir = realpath('../../uploads').'/';
                if(!is_dir($dir)) mkdir($dir);
                if(!is_dir($dir)) mkdir($dir);
                //文件名称
                $fileName = uniqid().'_'.date('His').'.'.$model->file->extension;
                //上传
                if($model->file->saveAs($dir.'/'.$fileName)){
                    //成功
                    $data = [
                        'name' => $fileName,
                        'type' => $model->file->type,
                        'size' => $model->file->size,
                        'filename' => realpath("../../uploads/").$fileName
                    ];
                    if($model->load($data)&&$model->save(false)){
                        $returnData = $model->attributes;
                        return $this->responseService->success($returnData, '上传成功!');
                    }
                }
                $msg = '上传失败!';
            } else {
                $msg = '';
            }
            return $this->responseService->error(422, Helper::getModelError($model));
        }
    }

}
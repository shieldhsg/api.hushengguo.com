<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class UploadFile extends ActiveRecord
{
    public $file;


    public function fields()
    {
        return [
            'id',
            'uuid',
            'original_name',
            'name',
            'type',
            'size',
            'extension',
            'path',
            'url',
            'filename',
            'ip' => function(){
                return long2ip($this->ip);
            }
        ];
    }

    //表单名称
    public function formName()
    {
        return '';
    }

    //表名
    public static function tableName()
    {
        return "{{%uploaded_file}}";
    }

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'maxSize'=>1024*1024*50, 'tooBig'=>'文件上传过大(50M以内)!'],
            [['uuid','original_name','name','type','size','extension','path','url','filename','ip'],'safe']
        ];
    }

}
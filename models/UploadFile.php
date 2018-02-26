<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class UploadFile extends ActiveRecord
{
    public $file;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'createtime',
                'updatedAtAttribute' => 'updatetime',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createtime', 'updatetime'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updatetime'],
                ]
            ]
        ];
    }

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
            'username',
            'ip' => function(){
                return long2ip($this->ip);
            },
            'createtime' => function(){
                return date('Y-m-d H:i', $this->createtime);
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
        return "{{%upload_file}}";
    }

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'maxSize'=>1024*1024*50, 'tooBig'=>'文件上传过大(50M以内)!'],
            [['uuid','original_name','name','type','size','extension','path','url','username','ip'],'safe']
        ];
    }

}
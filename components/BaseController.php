<?php
namespace app\components;

use app\services\JsonService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class BaseController extends Controller
{
    protected $jsonService;
    protected $permissionCode; //权限编码

    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        return [];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }

}
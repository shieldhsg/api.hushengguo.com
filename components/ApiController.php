<?php

namespace app\components;

use app\helpers\BaseinfoHelper;
use app\libs\Helper;
use app\libs\Menu;
use app\models\GrBaseInfo;
use app\models\GrProject;
use app\models\GrProjectAccident;
use app\models\GrProjectData;
use app\models\GrProjectSchedule;
use app\models\GrProjectVersion;
use app\models\GrStandard;
use app\models\GrStandardType;
use app\models\Log;
use app\models\User;
use app\modules\api\controllers\BaseinfoController;
use app\services\ResponseService;
use yii\base\Event;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use Yii;
use yii\filters\RateLimiter;
use yii\filters\Cors;

class ApiController extends ActiveController
{
    public $modelClass = '';
    public $optional = [
//        'options'
    ];
    public $except = [];
    //重写
    public $rewriteActions = [
        'update',
        'delete',
        'view',
        'create',
        'index',
//        'options' //默认支持OPTIONS请求
    ];
    public $notDetail = [
        'id',
        'project_id',
        'status',
        'state_info',
        'update_time',
        'user_id',
        'operation_user_id',
        'value',
        'order',
        'file_id'
    ]; //不记录详情的字段
    public $responseService;
    public $user; //当前登录用户
    public $log; //日志

    public $moduleName; //模块名
    public $functionalPlate; //功能板块
    public $operationName; //操作名称
    public $logAutomatic = 1; //是否自动收集日志 默认为1 自动收集 0 手动收集
    public $deminsion = []; //维度
    public $admin = 0;
    public $baseInfos= [];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
//                'Access-Control-Allow-Credentials' => true,
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'optional' => $this->optional,
            'except'=> ArrayHelper::merge(['options'], $this->except) //认证排除OPTIONS请求
        ];

        # rate limit部分，速度的设置是在
        #   app\models\User::getRateLimit($request, $action)
        /*  官方文档：
            当速率限制被激活，默认情况下每个响应将包含以下HTTP头发送 目前的速率限制信息：
            X-Rate-Limit-Limit: 同一个时间段所允许的请求的最大数目;
            X-Rate-Limit-Remaining: 在当前时间段内剩余的请求的数量;
            X-Rate-Limit-Reset: 为了得到最大请求数所等待的秒数。
            你可以禁用这些头信息通过配置 yii\filters\RateLimiter::enableRateLimitHeaders 为false, 就像在上面的代码示例所示。
        */
        $behaviors['rateLimiter'] = [
            'class' => RateLimiter::className(),
            'enableRateLimitHeaders' => true,
        ];

        return $behaviors;
    }

    //初始化
    public function init()
    {
       $this->responseService = new ResponseService();
       //兼容get方式请求的token
        if(empty(Yii::$app->request->getHeaders()->get('Authorization'))){
           $getToken = Yii::$app->request->get('token');
           Yii::$app->request->getHeaders()->set('Authorization',$getToken);
       }
       $this->baseInfos = (new BaseinfoHelper)->getAll();
       $this->user = $this->authenticate(Yii::$app->user, Yii::$app->request, Yii::$app->response);
        Event::on(BaseActiveRecord::className(),BaseActiveRecord::EVENT_AFTER_UPDATE,[$this,'log']);
        Event::on(BaseActiveRecord::className(),BaseActiveRecord::EVENT_AFTER_INSERT,[$this,'log']);
        $this->log = new Log();
    }

    public function actions()
    {
        $actions =  parent::actions();
        //判断是否需要重写的控制器
        if(!empty($this->rewriteActions)){
            foreach ($this->rewriteActions as $actionKey)
            {
                if(isset($actions[$actionKey])&&$actionKey!='options') unset($actions[$actionKey]);
            }
        }
        //设置固定options控制器
        $actions['options'] = [
            'class' => 'yii\rest\OptionsAction',
            // optional:
            'collectionOptions' => ['GET', 'POST', 'HEAD', 'OPTIONS'],
            'resourceOptions' => ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        ];
        return $actions;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        //接口权限处理
        $c = Yii::$app->controller->id;
        $a = Yii::$app->controller->action->id;
        //计算出当前的权限code
        $authCode = $c.'-'.$a;
        //判断是否是例外情况，跳过权限验证
        if ($this->except && in_array($a, $this->except)) return true;
        //判断是否登录失败，直接返回登陆失效
        if (empty($this->user)) {
            Yii::$app->response->statusCode = 401;
            Yii::$app->response->statusText = '登录失效!';
            return false;
        }
        //echo $authCode;die;
        //开始验证
        if($c && $a) {
            $data = Yii::$app->apiAuth->getPermissionArray($this->user->username);
            if (!empty($data)) {
                if (isset($data['admin'])) $this->admin = $permission['admin'] = $data['admin'];
                if (isset($data['data'])) $permission['list'] = array_keys($data['data']);
                    $this->deminsion = [];//获取维度
                    if($this->admin){
                        $studios =  GrBaseInfo::find()->select(['value'])->where(['pid'=>BaseinfoHelper::STUDIO,'status'=>1])->asArray()->all();
                        $studioArray = [];
                        foreach ($studios as $k => $v){
                            $studioArray[$k] = $v['value'];
                        }
                        $this->deminsion = $studioArray;
                    }
                    if(in_array($authCode, $permission['list'])){
                        //当权限列表中有当前节点的维度时
                        $deminsion = $data['data'][$authCode];
                        foreach ($deminsion as $k=>$v){
                            $deminArray = explode('-',$v);
                            $deminsion = $deminArray[1];
                            array_push($this->deminsion,$deminsion);
                        }
                    }elseif($permission['admin'] ==0){
                        //如果没有，并且不是管理员，则直接返回没有权限
                        return $this->authFailure();
                    }
            }
            }else{
                return $this->authFailure();
            }
        return true;
    }



    /**
     * 获取分页信息
     * @return array
     *      page  limit  offset
     */
    public function getPageInfo()
    {
        $page = (int)Yii::$app->request->get('page')??1;
        $page = $page<=0 ? 1 : $page;
        $limit = (int)Yii::$app->request->get('limit')??10;
        $limit = $limit<=0 ? 10 : $limit;
        $offset = ($page-1)*$limit;
        $data = [$page, $limit, $offset];
        return $data;
    }

    /**
     * 获取字符串类型的参数
     * @param $name
     * @param string $default
     * @author 伏火
     * @return array|mixed|string
     */
    public function getString($name, $default = '')
    {
        if (Yii::$app->request->isGet) {
            $res = Yii::$app->request->get($name);
            if ($res === null) {
                return null;
            } else {
                return $res ?? $default;
            }

        } elseif (Yii::$app->request->isPost || Yii::$app->request->isPut) {
            $res = Yii::$app->request->post($name);
            if ($res === null) {
                return null;
            } else {
                return $res ?? $default;
            }
        }
        return null;
    }

    /**
     * 获取整数类型的参数
     * @param $name
     * @param int $default
     * @author 伏火
     * @return array|int|mixed
     */
    public function getInt($name, $default = 0)
    {
        if (Yii::$app->request->isGet) {
            $res = Yii::$app->request->get($name) ?? $default;
        } elseif (Yii::$app->request->isPost || Yii::$app->request->isPut) {
            $res = Yii::$app->request->post($name) ?? $default;
        }
        return intval($res);
    }

    /**
     * 获取时间类型的参数
     * @param $name
     * @param string $default
     * @author 伏火
     * @return array|mixed|string
     */
    public function getTime($name, $default = '')
    {
        if (Yii::$app->request->isGet) {
            $res = Yii::$app->request->get($name);
            if ($res === null) {
                return null;
            } else {
                return date('Y-m-d H:i:s',strtotime($res))?? $default;
            }

        } elseif (Yii::$app->request->isPost || Yii::$app->request->isPut) {
            $res = Yii::$app->request->post($name);
            if ($res === null) {
                return null;
            } else {
                return date('Y-m-d H:i:s',strtotime($res))?? $default;
            }
        }
        return null;
    }
    /**
     * 检查必填项，传入格式为数组，key为必填项名称，value为必填项的值，如果没有为空的，则返回false,否则返回名称
     * @param $arr
     * @author 伏火
     * @return bool|int|string
     */
    public function checkItemsEmpty($arr)
    {
        if (empty($arr)) {
            return false;
        } else {
            foreach ($arr as $k => $v) {
                if (empty($v)) {
                    return $k;
                }
            }
        }
        return false;
    }



}
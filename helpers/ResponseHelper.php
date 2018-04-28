<?php
namespace app\helpers;

class ResponseHelper
{
    const SUCCESS = 200;
    const SUCCESS_MSG = '操作成功';


    // 40开头错误
    const DATA_NOT_FOUND = 404;
    const DATA_NOT_FOUND_MSG = '资源无法找到或已被管理员清理';
}
?>
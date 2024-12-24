<?php
declare (strict_types = 1);

namespace mzunit\uc;

use think\facade\Db;

// use mztp\MzDb;
use mztp\ext\MLog;
// use mzrely\jwt\FirebaseJwt;
// use mzclass\library\FilterCheck;

/**
 * 账号注册业务逻辑处理类
 *
 * @Author Mirze
 */
class RegUcMember
{
    // protected $mzDb = null;

    public $tableName = 'uc_member';
    public $jwtExpTime = 86400; // JWT数据过期时间：24小时

    // function __construct()
    // {
    //     if(empty($this->mzDb)) {
    //         $this->mzDb = new MzDb();
    //     }

    // }

    // 用户表名uc_member修改调用配置
    function setTableName($name='')
    {
        if(!is_string($name)) return false;
        $this->tableName = empty($name) ? $this->tableName : trim($name);
    }

    // JWT数据过期时间
    function setJwtExpTime($exp=0)
    {
        $this->jwtExpTime = $exp > 0 ? $exp : $this->jwtExpTime;
    }

    /**
     * WEB账号注册
     *
     * @param array $reqData    请求参数数组：
     *                              必须参数：account、pwd、phone
     *                              可选参数：realname、email
     * @return void [code,uid]
     */
    function account($reqData=[])
    {
        // $req = request()->param('','', 'trim'); // $reqData
        if(!is_array($reqData) || empty($reqData)) return [300116];

        $account = isset($reqData['account']) ? trim($reqData['account']) : '';
        $setpwd = isset($reqData['pwd']) ? trim($reqData['pwd']) : '';
        $phone = isset($reqData['phone']) ? trim($reqData['phone']) : '';
        $email = isset($reqData['email']) ? trim($reqData['email']) : '';
        $realname = isset($reqData['realname']) ? trim($reqData['realname']) : '';

        if($account == '' || $setpwd == '') return [401001];
        if($phone == '') return [300212];

        $lenAccount = strlen($account);
        if($lenAccount < 5 || $lenAccount > 30) return [401018];

        $lenPwd = strlen($setpwd);
        if($lenPwd < 6 || $lenPwd > 30) return [401020];

        // 校验账号和密码规则
        // $ruleAccount = '/^(?=.*\w).{5,30}$/';
        $ruleAccount = '/^[a-zA-Z0-9_]{5,30}$/';
        // $rulePwd = '/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[!@$%^&*]).{6,30}$/';
        $rulePwd = '/^(?=.*[0-9])(?=.*[a-zA-Z]).{6,30}$/';
        if(! preg_match($ruleAccount, $account)) return [401019];
        if(! preg_match($rulePwd, $setpwd)) return [401021];
        
        // 校验手机号
        // $checkPhone = FilterCheck::regularVerify($phone, 'mobile');
        // if(! $checkPhone) return [401022];
        $rulePhone = '/^1[2-9]\d{9}$/';
        if(! preg_match($rulePhone, $phone)) return [401022];

        // 校验邮箱
        if($email !=  '') {
            // $checkEmail = FilterCheck::regularVerify($email, 'email');
            // if(! $checkEmail) return [401023];
            $ruleEmail = '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/';
            if(! preg_match($ruleEmail, $email)) return [401023];
        }

        // 校验账号(排除已伪删除)是否已存在，手机号是否已绑定
        $raw = "state > -1 AND (account = :val1 OR phone = :val2)";
        $rawVal = [
            'val1' => $account,
            'val2' => $phone
        ];
        $recNum = Db::table($this->tableName)->whereRaw($raw, $rawVal)->count();
        if($recNum > 0) return [401024];

        $time = time();
        $ip = request()->ip();

        // 日志基本信息
        $logData['source'] = 'reg-account';
        $logData['account'] = $account;
        $logData['phone'] = $phone;
        $logData['reg_ip'] = $ip;
        $logData['reg_time'] = $time;

        $data['account'] = $account;
        $data['pwd'] = md5($setpwd);
        $data['phone'] = $phone;
        $data['email'] = $email;
        $data['realname'] = $realname;
        $data['state'] = 1;
        $data['opt_uid'] = 0;
        $data['reg_source'] = 1; // 注册渠道：0 未知 1 WEB 2微信扫码 3 钉钉扫码
        $data['create_time'] = $time;
        $data['update_time'] = $time;

        // $data['last_login_ip'] = $ip;
        // $data['last_login_time'] = $time;

        $uid = Db::table($this->tableName)->strict(false)->insertGetId($data);
        if($uid > 0) {
            $logData['reg_result'] = 1;
            $logData['reg_uid'] = $uid;
            MLog::reg($logData, 'info');

            return [1, $uid];
        }
        $logData['reg_result'] = 0;
        MLog::reg($logData, 'info');
        return [0];
    }




}
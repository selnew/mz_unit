<?php
declare (strict_types = 1);

namespace mzunit\uc;

use think\facade\Db;

use mztp\MzDb;
use mztp\ext\MLog;
use mzrely\jwt\FirebaseJwt;
use mzclass\library\FilterCheck;

/**
 * 账号登录业务逻辑处理类
 *
 * @Author Mirze
 */
class SignUcMember
{
    protected $mzDb = null;

    public $tableName = 'uc_member';
    public $jwtExpTime = 86400; // JWT数据过期时间：24小时

    function __construct()
    {
        if(empty($this->mzDb)) {
            $this->mzDb = new MzDb();
        }

    }

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

    function account($account, $pwd, $isJwt=false, $pwdMd5=false, $selFiled='')
    {
        if($account == '' || $pwd == '') return [401001];

        $md5Pwd = $pwdMd5 ? $pwd : md5($pwd); // 密码Md5
        $accountField = $selFiled == "" ? "account|phone" : trim($selFiled); // 账号登录方式字段：account/phone
        
        $map[] = [$accountField, '=', $account];
        // $map[] = ['pwd', '=', $pwdMd5];
        $map[] = ['state', '=', 1];
        
        // 获取记录
        $row = $this->mzDb->readRow($this->tableName, $map, "", false);
        // dd($row);

        $time = time();
        $ip = request()->ip();

        // 错误日志基本信息
        $logData['source'] = 'sign-account';
        $logData['account'] = $account;
        $logData['login_ip'] = $ip;
        $logData['login_time'] = $time;

        if(empty($row)) {
            // 登录失败日志
            $logData['error_code'] = '401017';
            $logData['pwd'] = $pwd;
            MLog::login($logData, 'error'); 

            return [401017];
        }

        // 验证密码
        $userPwd = empty($row['pwd']) ? '' : trim($row['pwd']);
        if($md5Pwd != $userPwd) {
            // 密码错误
            $logData['error_code'] = '401002';
            $logData['pwd'] = $pwd;
            MLog::login($logData, 'error'); 

            return [401002];
        }
        
        // JWT加密数据
        $data = [
            'uid' => $row['id'],
            'account' => empty($row['account']) ? '' : trim($row['account']),
            'realname' => empty($row['realname']) ? '' : trim($row['realname']),
            // 'company_id' => empty($row['company_id']) ? 0 : $row['company_id'],
            // 'dept_id' => empty($row['dept_id']) ? 0 : $row['dept_id'],
            // 'account_type' => empty($row['account_type']) ? 0 : $row['account_type'],
            // 'is_admin' => empty($row['is_admin']) ? 0 : $row['is_admin'],
            'login_time' => $time
        ];
        $access_token = md5(json_encode($data));

        // JWT
        if($isJwt) {
            // $jwtData['data'] = $data;

            $FJwt = new FirebaseJwt();
            $token = $FJwt->genToken($data, $this->jwtExpTime);
            $data['token'] = $token;
        }

        // 手机号校验安全加星处理
        $phone = isset($row['phone']) ? $row['phone'] : '';
        // 校验手机号
        $filterCheck = new FilterCheck();
        $checkPhone = $filterCheck->regularVerify($phone, 'mobile');
        if($checkPhone) {
            // 手机号安全加星处理
            $phone = preg_replace("/(\d{3})\d{4}(\d{4})/", "$1****$2", $phone);
        }

        $data['access_token'] = $access_token;
        // $data['phone'] = isset($row['phone']) ? $row['phone'] : '';
        $data['phone'] = $phone; // 安全加星
        $data['email'] = isset($row['email']) ? $row['email'] : '';
        $data['sex'] = empty($row['sex']) ? 0 : $row['sex'];
        $data['avatar_uri'] = isset($row['avatar_uri']) ? $row['avatar_uri'] : '';
        
        $data['wx_openid'] = isset($row['wx_openid']) ? $row['wx_openid'] : '';
        $data['ding_openid'] = isset($row['ding_openid']) ? $row['ding_openid'] : '';

        // $data['last_login_time'] = $row['login_time'];
        // $data['last_login_ip'] = $row['login_ip'];
        // $data['login_ip'] = $ip;
        // $data['login_time'] = $time;
        // dd($data);

        // 更新登录数据
        $upData['access_token'] = $access_token;
        $upData['last_login_time'] = $row['login_time'];
        $upData['last_login_ip'] = $row['login_ip'];
        $upData['login_ip'] = $ip;
        $upData['login_time'] = $time;
        $up = $this->mzDb->saveUpdate($this->tableName, 2, $upData, $row['id']);

        // 记录登录成功日志
        $logData['up_state'] = $up; // 更新结果
        $logData['uid'] = $row['id'];
        MLog::login($logData, 'ok'); // 记录登录成功日志

        return [1, $data];
    }

}
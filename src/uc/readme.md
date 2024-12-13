# use mzunit/uc;
UC mgr

## 结构
~~~
src
    uc          用户中心：登录
        SignUcMember.php   登录
    
~~~


## 相关表
~~~
CREATE TABLE `mz_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'uid',
  `account` varchar(50) NOT NULL COMMENT '账号',
  `pwd` varchar(50) NOT NULL COMMENT '密码',
  `phone` varchar(11) NOT NULL COMMENT '手机号',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `realname` varchar(30) NOT NULL COMMENT '真实姓名',
  `nickname` varchar(30) DEFAULT NULL COMMENT '昵称',
  `sex` tinyint(1) NOT NULL DEFAULT 0 COMMENT '性别：0未知1男2女',
  `avatar_uri` varchar(255) DEFAULT NULL COMMENT '头像',
  `wx_openid` varchar(50) DEFAULT '0' COMMENT '绑定微信openid',
  `ding_openid` varchar(50) DEFAULT '0' COMMENT '绑定钉钉openid',
  `qq_openid` varchar(50) DEFAULT NULL COMMENT '绑定QQopenid',
  `access_token` varchar(50) DEFAULT NULL COMMENT '登录token',
  `login_time` int(10) NOT NULL DEFAULT 0 COMMENT '登录时间',
  `login_ip` varchar(20) DEFAULT '0' COMMENT '登录IP',
  `last_login_time` int(10) NOT NULL DEFAULT 0 COMMENT '最近一次登录时间',
  `last_login_ip` varchar(20) DEFAULT NULL COMMENT '最近一次登录IP',
  `reg_source` tinyint(1) NOT NULL DEFAULT 0 COMMENT '注册渠道：0 未知 1 WEB 2微信扫码 3 钉钉扫码 3 qq',
  `login_allow` tinyint(1) NOT NULL DEFAULT 0 COMMENT '允许登录管理：0 未知 1 WEB登录 2 扫码登录 3 需修改密码 4 需完善资料 9 已完善',
  `state` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：-1删除0禁用1正常',
  `opt_uid` int(11) NOT NULL DEFAULT 0 COMMENT '操作人UID',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uk_account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='会员管理表';
    
~~~


## 使用
```
// 登录
use mzunit/uc/SignUcMember;

```
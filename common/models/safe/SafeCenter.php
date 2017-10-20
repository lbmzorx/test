<?php
/**
 * Created by PhpStorm.
 * User: 刘超富
 * QQ: 251651698
 * Date: 2017/8/17
 * Time: 12:41
 */
namespace common\models\safe;
use common\models\common\user\Users;
use common\tool\ApiCode;

class SafeCenter{
    public static $request_params;
    public static $user;
    public static $verify_identity_way=
        [
            1=>
                [
                    'name'=>'证件号',
                    'inspect_method'=>'\common\models\safe\SafeCenter::verify_card_id',
                ],
            2=>
                [
                    'name'=>'手机验证码',
                    'inspect_method'=>'\common\models\safe\SafeCenter::verify_mobile',
                ],
            3=>
                [
                    'name'=>'支付密码',
                    'inspect_method'=>'\common\models\safe\SafeCenter::verify_pay_password',
                ],
            4=>
                [
                    'name'=>'登录密码',
                    'inspect_method'=>'\common\models\safe\SafeCenter::verify_password',
                ],
            5=>
                [
                    'name'=>'邮箱验证码',
                    'inspect_method'=>'\common\models\safe\SafeCenter::verify_mail',
                ],
        ];
    public static $operation=[
        1=>[
            'name'=>'修改手机',
            'verify_way'=>
                [
                    1=>[2,1],//手机+证件号//注意顺序，先写先验证
                    2=>[5,1],//邮箱+证件号
                    3=>[2,'xxx'],//手机+证件号存在就不出现
                    4=>[5,'xxx'],//邮箱+证件号存在就不出现
                ],
            'operation_do_method'=>'up_mobile',
        ],
        2=>[
            'name'=>'修改邮箱',
            'verify_way'=>
                [
                    1=>[2,1],//证件号+手机
                    2=>[5,1],//证件号+邮箱
                    3=>[2,'xxx'],//手机+证件号存在就不出现
                    4=>[5,'xxx'],//邮箱+证件号存在就不出现
                ],
            'operation_do_method'=>'up_mail',
        ],
        3=>[
            'name'=>'修改密码',
            'verify_way'=>
                [
                    1=>[2,1],//证件号+手机
                    2=>[5,1],//证件号+邮箱
                ],
            'operation_do_method'=>'up_password',
        ],
        4=>[
            'name'=>'修改支付密码',
            'verify_way'=>
                [
                    1=>[2,1],//证件号+手机
                    2=>[5,1],//证件号+邮箱
                ],
            'common_view'=>3,
            'operation_do_method'=>'up_password',

        ],
        5=>[
            'name'=>'找回密码',
            'verify_way'=>
                [
                    1=>[2,1],//证件号+手机
                    2=>[5,1],//证件号+邮箱
                    3=>[2,'xxx'],//手机+证件号存在就不出现
                    4=>[5,'xxx'],//邮箱+证件号存在就不出现
                ],
            'common_view'=>3,
            'operation_do_method'=>'up_password',
        ],
        6=>[
            'name'=>'找回支付密码',
            'verify_way'=>
                [
                    1=>[1,2],//证件号+手机
                    2=>[1,5],//证件号+邮箱
                ],
            'common_view'=>3,
            'operation_do_method'=>'up_password',
        ],
        7=>[
            'name'=>'绑定邮箱',
            'verify_way'=>
                [
                    1=>[2],//手机
                ],
            'common_view'=>2,
            'operation_do_method'=>'up_mail',

        ],
        8=>[
            'name'=>'绑定手机',
            'verify_way'=>
                [
                    1=>[5],//邮箱
                ],
            'common_view'=>1,
            'operation_do_method'=>'up_mobile',

        ],
        9=>[
            'name'=>'解除手机',
            'verify_way'=>
                [
                    1=>[2],//手机
                ],
            'operation_do_method'=>'del_bind',
        ],
        10=>[
            'name'=>'解除邮箱',
            'verify_way'=>
                [
                    1=>[5],//邮箱
                ],
            'common_view'=>9,
            'operation_do_method'=>'del_bind',
        ],
        11=>[
            'name'=>'设置绑定手机为登录帐号',
            'verify_way'=>
                [
                    1=>[2],//手机
                ],
            'common_view'=>9,
            'operation_do_method'=>'set_login_account',
        ],
        12=>[
            'name'=>'设置绑定邮箱为登录帐号',
            'verify_way'=>
                [
                    1=>[5],//邮箱
                ],
            'common_view'=>9,
            'operation_do_method'=>'set_login_account',
        ],
        13=>[
            'name'=>'解除登录密码锁定',
            'verify_way'=>
                [
                    1=>[2,1],//手机+证件号//注意顺序，先写先验证
                    2=>[5,1],//邮箱+证件号
                    3=>[2,'xxx'],//手机+证件号存在就不出现
                    4=>[5,'xxx'],//邮箱+证件号存在就不出现
                ],
            'common_view'=>9,
            'operation_do_method'=>'unlock_password',
        ],
        14=>[
            'name'=>'解除支付密码锁定',
            'verify_way'=>
                [
                    1=>[2,1],//手机+证件号//注意顺序，先写先验证
                    2=>[5,1],//邮箱+证件号
                    3=>[2,'xxx'],//手机+证件号存在就不出现
                    4=>[5,'xxx'],//邮箱+证件号存在就不出现
                ],
            'common_view'=>9,
            'operation_do_method'=>'unlock_password',
        ],
    ];

    public function __construct($request_params)
    {
        self::$request_params=$request_params;
    }

    /**
     * 参数处理
     */
    public static function params($request_param,$way_id='',$null=1){
        $param=self::$request_params;
        if($null==1){
            $msg=$way_id?self::$verify_identity_way[$way_id]['name'].'不能为空':'必填项不能为空';
            empty($param[$request_param])? exit(json_encode(['status'=>false,'msg'=>$msg],JSON_UNESCAPED_UNICODE)):$value=$param[$request_param];
        }else{
            $value= isset($param[$request_param]) ? $param[$request_param]:null;
        }
        return $value;
    }

    /**
     * 检查验证方式是否可用
     */
    public static function inspect_verify_way_isnull($verify_identity_way,$user_id){
        $user=\Yii::$app->session->get('user');
        if(!$user){
            $user= \common\models\datamodels\Users::findOne($user_id);
        }
        if(!$user){
            return ['status'=>false,'msg'=>'用户不存在'];
        }
        switch ($verify_identity_way){
            case 1://card_id,证件号
                $user_info=Users::user_info($user_id,$user['type']);
                if(!$user_info){
                    return ['status'=>false,'msg'=>'用户信息不存在'];
                }
                $zengjianhao_field='cred_number';
                if($user['type']==1){
                    $zengjianhao_field='zenjianhao';
                }
                if(!$user_info[$zengjianhao_field] || !$user_info['name'] || $user_info['status']!=1){
                    return ['status'=>false,'msg'=>'用户基础信息未审核通过或未填写'];
                }
                return ['status'=>true];

            case 2://手机验证码
                if(!$user['bind_mobile']){
                    return ['status'=>false,'msg'=>'用户未绑定手机'];
                }
                return ['status'=>true];

            case 3://支付密码
                if(!$user['pay_password']){
                    return ['status'=>false,'msg'=>'用户未设置支付密码'];
                }
                return ['status'=>true];

            case 4://登录密码
                if(!$user['password']){
                    return ['status'=>false,'msg'=>'用户未设置密码'];
                }
                return ['status'=>true];
            case 5:
                if(!$user['bind_mail']){
                    return ['status'=>false,'msg'=>'用户未绑定邮箱'];
                }
                return ['status'=>true];
            case 'xxx':
                if(!self::inspect_verify_way_isnull(1,$user_id)['status']){
                    return ['status'=>true];//证件不存在时，返回真（用在用户证件不存在时才出现的验证组合）
                }
                return ['status'=>false,'msg'=>'用户证件存在'];
            default:
               break;
        }
        return ['status'=>false,'msg'=>'未知验证类型'];
    }

    /**
     * 检查可用验证方式组合
     */
    public static function inspect_operation_verify($operation,$user_id){
        if(!in_array($operation,array_keys(self::$operation))){
            return ['status'=>false,'msg'=>'非法操作'];
        }
        $way=self::$operation[$operation]['verify_way'];
        foreach ($way as $k=>$v){//组合数/如3对组合
            foreach ($v as $kk=>$vv){//组合关系，将每一种校验方式检测以便
                $inspect_verify_way_isnull=self::inspect_verify_way_isnull($vv,$user_id);
                if(!$inspect_verify_way_isnull['status']){
                    unset($way[$k]);
                    continue;
                }
            }
        }
        return ['status'=>true,'verify_way'=>$way];
    }

    /**
     * 执行各种验证方式的方法
     */
    public static function inspect_method($params,$ways,$user_id){
        self::$request_params=$params;
        self::$user=\common\models\datamodels\Users::findOne($user_id);
        foreach ($ways as $k=>$v){
            if(!is_numeric($v)){
                continue;
            }
            $inspect_method=(self::$verify_identity_way[$v]['inspect_method'])();
            if (!$inspect_method['status']){
                return $inspect_method;
            }
        }
        return ['status'=>true,'msg'=>'校验成功'];
    }

    /**
     * 验证邮箱验证码
     */
    public static function verify_mail(){
        $mail_verify_code=self::params('mail_verify_code',5);
        $inspect_mail_verify_code=MailMsgNotice::check_verify_code('信息变更',self::$user['bind_mail'],$mail_verify_code,$un=true);
        if(!$inspect_mail_verify_code['status']){
            return $inspect_mail_verify_code;
        }
        return ['status'=>true];
    }

    /**
     * 验证证件号
     */
    public static function verify_card_id(){
        $zenjianhao=self::params('zenjianhao',1);
        $user_info=Users::user_info(self::$user['id'],self::$user['type']);
        if(!$user_info){
            return ['status'=>false,'msg'=>'证件信息不存在，无法验证'];
        }
        $zenjianhao_field='cred_number';
        if(self::$user['type']==1){
            $zenjianhao_field='zenjianhao';
        }
        if(!$user_info[$zenjianhao_field]){
            return ['status'=>false,'msg'=>'证件号不存在'];
        }
        if($user_info[$zenjianhao_field]!=$zenjianhao){
            return ['status'=>false,'msg'=>'证件号错误'];
        }
        return ['status'=>true];
    }

    /**
     * 验证手机验证码
     */
    public static function verify_mobile(){
        $mobile_verify_code=self::params('mobile_verify_code',2);
        $inspect_mobile_verify_code= MobileMsgNotice::check_verify_code('信息变更',self::$user['bind_mobile'],$mobile_verify_code);
        if(!$inspect_mobile_verify_code['status']){
            return $inspect_mobile_verify_code;
        }
        return ['status'=>true];
    }

    /**
     * 验证登录密码
     */
    public static function verify_password(){
        $password=self::params('password',3);
        $inspect=UserPassword::verifyUserPassword('login',self::$user['id'],$password,true);
        if(!$inspect['status']){
            return $inspect;
        }
        return ['status'=>true];
    }

    /**
     * 验证支付密码
     */
    public static function verify_pay_password(){
        $pay_password=self::params('pay_password',4);
        $inspect=UserPassword::verifyUserPassword('pay',self::$user['id'],$pay_password,true);
        if(!$inspect['status']){
            return $inspect;
        }
        return ['status'=>true];
    }

    /**
     * 获取验证组合中文名
     */
    public static function get_verify_ways_name($operation_id,$group_id,$joint_mark='+'){
        $way_name=$way_ids=[];

        foreach ((self::$operation[$operation_id]['verify_way'][$group_id]) as $v){
            $way_ids[]=$v;
            if(!is_numeric($v)){
                continue;
            }
            $way_name[]= SafeCenter::$verify_identity_way[$v]['name'];
        }
        $way_name=implode($joint_mark,$way_name);
        return ['ways_name'=>$way_name,'ways_ids'=>$way_ids];;
    }
}
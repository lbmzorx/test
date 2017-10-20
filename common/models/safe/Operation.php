<?php
/**
 * Created by PhpStorm.
 * User: 刘超富
 * QQ: 251651698
 * Date: 2017/8/18
 * Time: 23:15
 */
namespace common\models\safe;
use common\models\datamodels\Users;

class Operation extends SafeCenter {
    public static $user;
    public static $operation_id;

    /**
     * 执行操作方法，只需传操作类型、参数和用户id即可自动调用
     */
    public static function operation_do($operation,$request_params,$user_id){
        self::$request_params=$request_params;
        self::$user=Users::findOne($user_id);
        self::$operation_id=$operation;
        $do= self::$operation[$operation]['operation_do_method'];
        return self::$do();
    }

    /**
     * 修改手机---1，8
     */
    public static function up_mobile(){
        $new_mobile=self::params('mobile');
        $mobile_verify_code=self::params('mobile_verify_code');
        $inspect_mobile_verify_code= MobileMsgNotice::check_verify_code('信息变更',$new_mobile,$mobile_verify_code);
        if(!$inspect_mobile_verify_code['status']){
            return $inspect_mobile_verify_code;
        }
        $set_login=self::params('is_user','',0);
        if($set_login=='yes'){
            self::$user->mobile=$new_mobile;
            self::$user->bind_mobile=$new_mobile;
        }
        self::$user->mobile=null;
        self::$user->bind_mobile=$new_mobile;
        if(!self::$user->save()){
            return ['status'=>false,'msg'=>'保存失败'];
        }
        return ['status'=>true];
    }

    /**
     * 修改邮箱---2，7
     */
    public static function up_mail(){
        $mail_verify_code=self::params('mail_verify_code');
        $new_mail=self::params('mail');
        $inspect_mail_verify_code=MailMsgNotice::check_verify_code('信息变更',$new_mail,$mail_verify_code,$un=true);
        if(!$inspect_mail_verify_code['status']){
            return  $inspect_mail_verify_code;
        }
        $set_login=self::params('is_user','',0);
        if($set_login=='yes'){
            self::$user->mail=$new_mail;
            self::$user->bind_mail=$new_mail;
        }
        self::$user->mail=null;
        self::$user->bind_mail=$new_mail;
        if(!self::$user->save()){
            return ['status'=>false,'msg'=>'保存失败'];
        };
        return ['status'=>true];
    }

    /**
     * 修改密码---3，4，5，6
     */
    public static function up_password(){
        $new_password=self::params('new_password');
        $confirm_password=self::params('confirm_password');
        if($new_password!=$confirm_password){
            return ['status'=>false,'msg'=>'两次输入的密码不一致'];
        }
        if(self::$operation_id==3 || self::$operation_id==5){
            $set=UserPassword::setUserPassword('login',$new_password);
            if(!$set['status']){
               return $set;
            }
            if(self::$user->password==$set['data']){
                return ['status'=>false,'msg'=>'新密码不能与旧密码相同'];
            }
            self::$user->password=$set['data'];
        }elseif (self::$operation_id==4 || self::$operation_id==6){
            $set=UserPassword::setUserPassword('pay',$new_password);
            if(!$set['status']){
                return $set;
            }
            if(self::$user->pay_password==$set['data']){
                return ['status'=>false,'msg'=>'新密码不能与旧密码相同'];
            }
            self::$user->pay_password=$set['data'];
        }else{
            return ['status'=>false,'msg'=>'错误的操作类型'];
        }

        if(self::$user->pay_password==self::$user->password){
            return ['status'=>false,'msg'=>'登录密码与支付密码不能相同！'];
        }
        if(!self::$user->save()){
            return ['status'=>false,'msg'=>'保存失败'];
        }
        return ['status'=>true];
    }

    /**
     * 设为解除绑定---9，10
     */
    public static function del_bind(){
        if(self::$operation_id==9){
            if(!self::$user->bind_mail){
                return ['status'=>false,'msg'=>'邮箱或手机必须绑定一个'];
            }
            self::$user->mobile=null;
            self::$user->bind_mobile=null;
        }elseif (self::$operation_id==12){
            if(!self::$user->bind_mobile){
                return ['status'=>false,'msg'=>'邮箱或手机必须绑定一个'];
            }
            self::$user->mail=null;
            self::$user->bind_mail=null;
        }else{
            return ['status'=>false,'msg'=>'错误的操作类型'];
        }
        if(!self::$user->save()){
            return ['status'=>false,'msg'=>'保存失败'];
        }
        return ['status'=>true];
    }

    /**
     * 设为登录帐号---11，12
     */
    public static function set_login_account(){
        if(self::$operation_id==11){
            if(Users::find()->where(['mobile'=>self::$user->bind_mobile])->exists()){
                return ['status'=>false,'msg'=>'该手机已被其他账户设为登录帐号，如果该手机是您的可登录解绑后继续'];
            }
            self::$user->mobile= self::$user->bind_mobile;
        }elseif (self::$operation_id==12){
            if(Users::find()->where(['mail'=>self::$user->bind_mail])->exists()){
                return ['status'=>false,'msg'=>'该邮箱已被其他账户设为登录帐号，如果该邮箱是您的可登录解绑后继续'];
            }
            self::$user->mail= self::$user->bind_mail;
        }else{
            return ['status'=>false,'msg'=>'错误的操作类型'];
        }
        if(!self::$user->save()){
            return ['status'=>false,'msg'=>'保存失败'];
        }
        return ['status'=>true];
    }

    /**
     * 解锁密码---13，14
     */
    public static function unlock_password(){
        if(self::$operation_id==13){
            self::$user->password_error_num= 0;
        }elseif (self::$operation_id==14){
            self::$user->pay_password_error_num=0;
        }else{
            return ['status'=>false,'msg'=>'错误的操作类型'];
        }
        if(!self::$user->save()){
            return ['status'=>false,'msg'=>'保存失败'];
        }
        return ['status'=>true];
    }

}

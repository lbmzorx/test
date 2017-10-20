<?php
/**
 * Created by PhpStorm.
 * User: x_l
 * QQ:99628038@qq.com
 * Date: 2017/1/10
 * Time: 12:04
 */
namespace common\models\safe;
class MailMsgNotice{

    public  function send_mail_verify_code($type,$mail){
        $verify_code=rand(111111,999999);
        $mobileSession=\Yii::$app->session->get(md5($mail.MobileMsgNotice::$type_key[$type]['key']));
        if($mobileSession&&isset($mobileSession['expire_time'])&&$mobileSession['expire_time']>time()){
            return ['status'=>false,'msg'=>'您的操作频率过于频繁，邮箱验证码正在发送，请等待'.($mobileSession['expire_time']-time()).'秒'];
        }
        \Yii::$app->session->set(md5($mail.MobileMsgNotice::$type_key[$type]['key']),['verify_code'=>$verify_code,'expire_time'=>(time()+60*2)]);
        \Yii::$app->session->remove(md5($mail.MobileMsgNotice::$type_key[$type]['key']).'mail_err');
        $send_status = $this->send_mail_msg($mail,'您正在'.$type.'您的验证码','您的验证码是：'.$verify_code.'请尽快完成操作');
        if($send_status){
            return ['status'=>true,'msg'=>'发送成功'.$verify_code];
        }else{
            return ['status'=>true,'msg'=>'发送失败'];
        }
    }

    /**
     *  检测验证码
     * @param $type
     * @param $mail
     * @param $verify_code
     * @param bool $un
     * @return array
     */
    public static function check_verify_code($type,$mail,$verify_code,$un=true){
        $verify_code_key=md5($mail.MobileMsgNotice::$type_key[$type]['key']);
        $session_code=\Yii::$app->session->get($verify_code_key);
        if($session_code['expire_time']<time()){
            return ['status' => false, 'msg' =>'验证码已经过期，请重新发送'];
        }
        if(!$session_code|| !isset($session_code['verify_code'])|| $verify_code!=$session_code['verify_code']) {
            $err_verify_key=$verify_code_key.'mail_err';

            \Yii::$app->session->set($err_verify_key,\Yii::$app->session->get($err_verify_key)+1);
            $err_num=\Yii::$app->session->get($err_verify_key);

            if($err_num>3){
                return ['status' => false, 'msg' =>'验证次数过多，请重新发送'];
            }

            return ['status' => false, 'msg' => '邮箱验证码错误'];
        }

        if($un==true){
            self::un_verify_code($verify_code_key);
        }

        return ['status'=>true];
    }

    /**
     * 销毁验证码
     * @param $key
     */
    public static function un_verify_code($verify_code_key){
        \Yii::$app->session->remove($verify_code_key);
    }

    public  function send_mail_msg($mail,$title,$content){
        return \Yii::$app->mailer->compose()
            ->setFrom('os@1zbao.com')
            ->setTo($mail)
            ->setSubject($title)
            ->setHtmlBody($content)
            ->send();
    }

}
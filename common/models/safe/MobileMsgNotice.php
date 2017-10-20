<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/1/10
 * Time: 12:04
 */
namespace common\models\safe;
use Sms\Request\V20160927 as Sms;
include_once \Yii::$app->basePath.DIRECTORY_SEPARATOR.'sdk'.DIRECTORY_SEPARATOR.'aliyun-php-sdk-sms'.DIRECTORY_SEPARATOR.'aliyun-php-sdk-core'.DIRECTORY_SEPARATOR.'Config.php';
class MobileMsgNotice
{
    protected $requeset;
    protected $client;

    public static $type_key=['修改支付密码'=>['key'=>'modifi_pay_password','tid'=>'SMS_38800061'],
                            '修改的登录密码'=>['key'=>'modifi_login_password','tid'=>'SMS_37595173'],
                            '找回登录密码'=>['key'=>'back_user_passsword','tid'=>'SMS_37595179'],
                            '找回支付密码'=>['key'=>'back_pay_passsword','tid'=>'SMS_37595179'],
                            '用户注册'=>['key'=>'user_login','tid'=>'SMS_37595175'],
                            '信息变更'=>['key'=>'user_info_edit','tid'=>'SMS_37595172']
    ];

    function __construct()
    {
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", "LTAIiHsK31eeCwDk", "yCRAXo8Zl452e9pepmk30D2fcorXui");
        $this->client = new \DefaultAcsClient($iClientProfile);
        $this->request = new Sms\SingleSendSmsRequest();
        //签名名称
        $this->request->setSignName("你行我动");
    }

    /**
     * 获取类型
     * @param $key
     * @return bool|int|string
     */
    public static function get_type_key($key){
        foreach (self::$type_key as $k=>$v){
            if($key==$v['key']){
                return $k;
            }
        }
        return false;
    }

    /**发送手机验证码
     * @param $mobile
     */
    public  function send_verify_code($type,$mobile){
        if(!isset(self::$type_key[$type])){
            throw new  \Exception('错误的验证码KEY');
        }
        $mobileSession=\Yii::$app->session->get(md5($mobile.self::$type_key[$type]['key']));
        if($mobileSession&&isset($mobileSession['expire_time'])&&$mobileSession['expire_time']>time()){
            return ['status'=>false,'msg'=>'上一个手机验证码正在发送，请等待'.($mobileSession['expire_time']-time()).'秒'];
        }

        $verify_code=rand(111111,999999);
        \Yii::$app->session->set(md5($mobile.self::$type_key[$type]['key']),['verify_code'=>$verify_code,'expire_time'=>(time()+60)]);
        \Yii::$app->session->remove(md5($mobile.self::$type_key[$type]['key']).'_err');
        $this->send_mobile_msg($mobile,self::$type_key[$type]['tid'],"{code:'$verify_code',product:'狐豆'}");
        return ['status'=>true,'msg'=>'发送成功'.$verify_code];
    }

    /**
     * 检测验证码
     * 增加手机验证码时间
     * @param $type
     * @param $mobile
     * @param $verify_code
     * @param bool $un
     * @return array
     * @throws \Exception
     */
    public static function check_verify_code($type,$mobile,$verify_code,$un=true){
        if(!isset(self::$type_key[$type])){
            throw new  \Exception('错误的验证码KEY');
        }

        $verify_code_key=md5($mobile.self::$type_key[$type]['key']);
        $err_verify_key=$verify_code_key.'_err';

        $session_code=\Yii::$app->session->get($verify_code_key);

        if($session_code['expire_time']<time()){
            \Yii::$app->session->remove($verify_code_key);
            return ['status' => false, 'msg' =>'验证码已经过期，请重新发送'];
        }
        if(!$session_code['verify_code'] || $verify_code!=$session_code['verify_code']) {
            \Yii::$app->session->set($err_verify_key,\Yii::$app->session->get($err_verify_key)+1);
            $err_num=\Yii::$app->session->get($err_verify_key);
            if($err_num>3){
                return ['status' => false, 'msg' =>'验证次数过多，请重新发送'];
            }
            return ['status' => false, 'msg' => '手机验证码错误'];
        }


        if($un==true){
            \Yii::$app->session->remove($verify_code_key);
        }
        return ['status'=>true];
    }

    /**
     * 销毁手机验证码
     * @param $key
     */
    public static function un_verify_code($type,$key){
        if(!isset(self::$type_key[$type])){
            throw new  \Exception('错误的验证码KEY');
        }

        $verify_code_key=md5($key.self::$type_key[$type]['key']);
        \Yii::$app->session->remove($verify_code_key);
    }

    public  function send_mobile_msg($mobile,$tmp,$content){
        //模板code
        $this->request->setTemplateCode($tmp);
        //目标手机号
        $this->request->setRecNum($mobile);
        //模板变量，数字一定要转换为字符串
        $this->request->setParamString($content);

    }

    function __destruct()
    {
        // 析构处理
        try {
            $response = $this->client->getAcsResponse($this->request);
           // print_r($response);
        }
        catch (ClientException  $e) {
            print_r($e->getErrorCode());
            print_r($e->getErrorMessage());
        }
        catch (ServerException  $e) {
            print_r($e->getErrorCode());
            print_r($e->getErrorMessage());
        }
    }
}
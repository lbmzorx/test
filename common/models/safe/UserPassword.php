<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/19
 * Time: 16:27
 */
namespace common\models\safe;
use common\models\common\user\Users;

class UserPassword
{

    /**
     * 公钥
     * @var
     */
    private $pubKey;
    /**
     * 私钥
     * @var
     */
    private $privKey;
    /**
     * 有效期限 10分钟
     * @var
     */
    private $expire;
    /**
     * 配置
     * @var
     */
    private $config=array(
        "config" => 'D:/phpStudy/Apache/conf/openssl.cnf',		//如果没有添加OPENSSL_CONF环境变量，必须的配置
        //  "digest_alg" => "sha512",
        "private_key_bits" => 1024,		//512,1024,2048,4096
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    public function __construct($config=[]){
        foreach ($config as $key=>$value){
            $this->config[$key]=$value;
        }
    }

    public function createNewRSA(){
        // Create the private and public key
        $res = openssl_pkey_new($this->config);

        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey, null, $this->config);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        $this->privKey=$privKey;
        $this->pubKey=$pubKey;
        $this->expire=time()+60*10;
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
        return $this->$name=$value;
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        return $this->$name;
    }

    /**
     * 使用公钥加密 数据
     * @param string $dataString
     * @return mixed
     */
    public function publicKeyEncode($dataString=''){
        // Encrypt the data to $encrypted using the public key
        $status= openssl_public_encrypt($dataString, $encrypted, $this->pubKey);
        return $status?$encrypted:false;
    }
    /**
     * 使用私钥解密数据
     * @param $encrypted
     * @return mixed
     */
    public function privateKeyDecode($encrypted){
        // Decrypt the data using the private key and store the results in $decrypted
        $status= openssl_private_decrypt($encrypted, $decrypted, $this->privKey);
        return $status?$decrypted:false;
    }

    /**
     * 使用私钥加密 数据
     * @param string $dataString
     * @return bool
     */
    public function privateKeyEncode($dataString=''){
       $status= openssl_private_encrypt($dataString,$encrypted,$this->privKey);
       return $status?$encrypted:false;
    }
    /**
     * 使用公钥解密数据
     * @param $encrypted
     * @return bool
     */
    public function publicKeyDecode($encrypted){
       $status= openssl_public_decrypt($encrypted, $decrypted, $this->privKey);
        return $status?$decrypted:false;
    }

    public function transCodeLine(){
        $this->privKey=str_replace("\n",'#',$this->privKey);
        $this->pubKey=str_replace("\n",'#',$this->pubKey);
    }

    public function tranCodeNormal(){
        $this->privKey=str_replace('#',"\n",$this->privKey);
        $this->pubKey=str_replace('#',"\n",$this->pubKey);
    }

    /**
     * 保存密钥到session中
     * @param $key
     */
    public function saveRSAKeyInSession($key){
        $this->transCodeLine();
        \yii::$app->session->set($key.'RSA',['privKey'=>$this->privKey,'pubKey'=>$this->pubKey,'expire'=>$this->expire]);
    }
    /**
     * 获取密钥在session中
     * @param $key
     */
    public function getRSAKEYInSession($key){
        $key = \yii::$app->session->get($key.'RSA');
        $this->privKey=$key['privKey'];
        $this->pubKey=$key['pubKey'];
        $this->expire=$key['expire'];
        $this->tranCodeNormal();
    }

    /**
     * 验证用户密码
     * 待完善
     */
    static function verifyUserPassword($type,$user_name,$password,$by_id=false,$return_user_object=false){
        if(!$user_name){
            return ['status'=>false,'msg'=>'用户名不能为空'];
        }
        if(!$password){
            return ['status'=>false,'msg'=>'密码不能为空'];
        }

        switch ($type){
            case 'login':
                $passwordfied='password';
                $password_error_num_filed='password_error_num';
                break;
            case 'pay':
                $passwordfied='pay_password';
                $password_error_num_filed='pay_password_error_num';
                break;
            default :
                return false;
                break;
        }
        $user=null;
        if($by_id==true){
            $user=Users::find()->where(['id'=>$user_name])->one();
        }else{
            $user=Users::find()->where(['or',['user_name'=>$user_name],
                ['mobile'=>$user_name],['mail'=>$user_name]])->one();
        }
        if(!$user){
            return ['status'=>false,'msg'=>'用户不存在'];
        }
        $password_error_num=$user[$password_error_num_filed];
        if($password_error_num >= 20){
            $user->$password_error_num_filed=$password_error_num+1;
            $user->status=2;
            $user->save();
            \Yii::$app->session->set('user',null);
            return ['status'=>false,'msg'=>'密码错误超过3次,账户已被锁定，如需帮助请联系客服'];
        }

        if(static::validatePassword($password,$user[$passwordfied])){
            $user->$password_error_num_filed=0;
            $user->save();
            if($return_user_object==true){
                return ['status'=>true,'msg'=>'验证通过','user_object'=>$user];
            }
            return ['status'=>true,'msg'=>'验证通过'];
        }
        $user->$password_error_num_filed=$password_error_num+1;
        $user->save();
        return ['status'=>false,'msg'=>'密码错误'];
    }

    /**
     * 生成用户密码
     */
    public static function setUserPassword($type,$password){
        if(!$type || !$password){
            return ['status'=>false,'msg'=>'类型错误'];
        }
        switch ($type){
            case 'login':
                $data=static::setPassword($password);
                return $data?['status'=>true,'msg'=>'生成成功','data'=>$data]:['status'=>false,'msg'=>'生成错误'];
                break;
            case 'pay':
                $data=static::setPassword($password);
                return $data?['status'=>true,'msg'=>'生成成功','data'=>$data]:['status'=>false,'msg'=>'生成错误'];
                break;
        }
        return ['status'=>false,'msg'=>'未知类型'];
    }

    /*---------------------------------------------由yii2设置的密码--------------------------------------------------*/
    /**
     * 验证密码
     * @param $password_frontend
     * @param $password_backend
     * @return bool
     */
    public static function validatePassword($password_frontend,$password_backend){
        return \Yii::$app->security->validatePassword($password_frontend, $password_backend);
    }

    /**
     * 设置密码
     * @param $password
     */
    public static function setPassword($password){
        return \Yii::$app->security->generatePasswordHash($password);
    }

}
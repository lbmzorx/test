<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2017/6/14
 * Time: 15:34
 */

namespace common\tool;
use yii\base\Exception;

class ApiCode
{
    public  $use_method_code;
    public  $codespace;//调用
    public  $code_type;//调用
    public  $result_code=[];//初始化

    public  $set_codes=[];//设置待取的code
    public  $view_codes=[];//要显示的code


    public function servie_code($codespace, $mobile_code,$type=2)
    {
        $inspect_code=[];//检测重复
        foreach ($mobile_code as $method => $codes) {
            foreach ($codes as $code => $msg) {
                $refer_code= $type . sprintf("%04d", self::str_to_num($codespace . $method)) . sprintf("%02d", $code);
                array_push($inspect_code, $refer_code);
                $this->result_code[$method][$code] = ['status'=>false, 'msg' => $msg,'code' => $refer_code,'method'=>$codespace .'.'. $method];
                $this->view_codes[$refer_code.'code']=['code'=>$refer_code,'msg'=>$msg];
            }
        }
        if (count($inspect_code) != count(array_unique($inspect_code))) {
            throw new Exception('警告！错误码出现重复使用');
        }
        return $this->result_code;
    }

    //get_code返回整个$method主体，注意代码块返回false时才能调用
    public  function get_codes($method){
        return $this->result_code[$method];
    }
    //view_codes初始化
    public static function view_codes($codespace,$const_code,$type=2){
        $codes_obj=new \common\tool\ApiCode();
        $codes_obj->servie_code($codespace,$const_code,$type);
        return $codes_obj->view_codes;
    }
    public static function get_view_codes($request_method){
        $a=explode('.',$request_method);
        $method=array_pop($a);
        $models=ucfirst(array_pop($a));
        try {
            if ($models) {
                return ('\\common\\models\\api\\servicemall\\'.$models)::get_view_codes();
            } else {
                return false;
            }
            return false;
        } catch (Exception $e) {
            return false;
            exit();
        }
    }
    //将字符变成数字
    public static function str_to_num($str)
    {
        $num = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $num += ord(substr($str, $i, 1)) - 48;
        }
        return $num;
    }

    public static function init_code($codespace,$method,$const_code,$type=2){
        $codes_obj=new \common\tool\ApiCode();
        $codes_obj->servie_code($codespace,$const_code,$type);
        $codes=$codes_obj->get_codes($method);//获取错误码
        return $codes;
    }

//



    public function direct_echo_code($code){
        return self::echo_code(['status'=>false,'msg'=>$this->use_method_code[$code],'code'=>$code,'method'=>array_keys($this->use_method_code)[0],'codespace'=>$this->codespace],$this->code_type);
    }


    //----------------------------------业务模型或封装函数用以下方法定义code

    public static function echo_code($result_code,$type=2){
        $code= $type . sprintf("%04d", self::str_to_num($result_code['codespace'] . $result_code['method'])) . sprintf("%02d", $result_code['code']);
        $codes=['status'=>$result_code['status'],'msg'=>$result_code['msg'],'code'=>$code];
        return $codes;
    }

    //外部方法调用时，初始化
    public function set_method($codespace,$method,$const_codes,$code_type=''){
        $this->code_type=$code_type;
        $this->codespace=$codespace;
        $this->use_method_code=$const_codes[$method];
    }
//renturn时调用
    public function set_code($code){
        return ['status'=>false,'msg'=>$this->use_method_code[$code],'code'=>$code,'method'=>array_keys($this->use_method_code)[0],'codespace'=>$this->codespace];
    }

}

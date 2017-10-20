<?php
/**
 * http 请求工具包
 * User: admin
 * Date: 2016/9/22
 * Time: 15:07
 */

namespace common\tool;


class http
{
    /**
     * 字符串参数型 http请求
     * @param $url
     * @param $data 字符串参数
     * @return bool|mixed 请求错误返回false|正确返回数据
     */
    public static function http_str_post($url,$data,$user_ip=''){
        $user_ip=$user_ip?$user_ip:\Yii::$app->request->userIP;
        //curl初始化
        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$user_ip, 'CLIENT-IP:'.$user_ip));//IP
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS,$data);
        //https 请求
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $return = curl_exec ( $ch );
        //出错检测
        if(curl_errno($ch)){
            echo "CURL错误代码为".curl_errno($ch);
            return false;
        }else{
            return $return;
        }
        curl_close ($ch);
    }
    public static function http_str_cookie($url,$data,$cookie){
        //curl初始化
        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt ($ch, CURLOPT_USERAGENT,  "Mozilla/5.".rand(0,100)." (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //https 请求
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $return = curl_exec ( $ch );
    }
}
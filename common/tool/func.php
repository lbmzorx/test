<?php
/**
 * Created by PhpStorm.
 * User: x_l
 * QQ:99628038
 * Date: 2016/12/15
 * Time: 18:12
 */

namespace common\tool;

use cszchen\citizenid\Parser;

class func
{

    /**
     * @param $name
     * @param $senfen_number
     * @return bool|mixed|string
     */
    public static function check_senfen($name,$senfen_number){
        $senfen_number=strval($senfen_number);
        $p=new Parser();
        if(!$p->isValidate($senfen_number)){
            return '证件格式不合法';
        }
        $err_info=['00'=>'正确','0200'=>'证件格式不合法','0201'=>'证件格式不合法','0202'=>'证件格式不合法'
            ,'0203'=>'很抱歉，您的证件号码为黑名单，暂不能办理','9999'=>'很抱歉，您的证件号码为黑名单，暂不能办理'
            ,'0204'=>'很抱歉，您的证件号码办理的号码数过多，暂不能办理','0205'=>'很抱歉，您的证件号码下有号码为欠费，暂不能办理'
            ,'01'=>'非常抱歉，您的入网证件未在公安系统登记'
            ,'02'=>'名与证件号码不一致，请重新输入'
            ,'03'=>'非常抱歉，公安系统暂时关闭','04'=>'非常抱歉，公安系统暂时关闭'
            ,'99'=>'尊敬的客户目前办理的用户较多，暂时无法为您提供服务,请稍后再试'
        ];
        $url="http://www.10010.com/mall-web/OrderInputAjaxNew/checkCustInfo";
        $data="CertAdress=&bizType=normal&cityCode=591&goodsId=591403121719&idCardVal={$senfen_number}&opeSysType=2&provinceCode=59&psptTypeCode=02&receiverNameVal={$name}&tmplId=60000019";
        $err_code=\common\tool\http::http_str_post($url,$data);
        $err_code=json_decode($err_code,true);
        if(!isset($err_info[$err_code])){
            return false;
        }
        return $err_info[$err_code];
    }

    public static function str_xinahao($str,$code,$stratr,$end){
        $a=substr($str,$stratr,$end);
        return str_replace($a,str_repeat($code,strlen($a)),$str);
    }

    /**
     * 用php从身份证中提取生日,包括15位和18位身份证
     * @param $IDCard 身份证号码
     * @return mixed
     */
    public static function get_id_cardinfo($IDCard){
        if(!preg_match("/^[1-9]([0-9a-zA-Z]{17}|[0-9a-zA-Z]{14})$/",$IDCard)){
            $result['error']='格式错误';
            return $result;
        }

        if(strlen($IDCard)==18){
            $tyear=intval(substr($IDCard,6,4));
            $tmonth=intval(substr($IDCard,10,2));
            $tday=intval(substr($IDCard,12,2));
            $sexint = (int)substr($IDCard,16,1);
        }elseif(strlen($IDCard)==15){
            $tyear=intval("19".substr($IDCard,6,2));
            $tmonth=intval(substr($IDCard,8,2));
            $tday=intval(substr($IDCard,10,2));
            $sexint = (int)substr($IDCard,14,1);
        }

        $result['sex']=$sexint % 2 === 0 ? '女' : '男';

        if($tyear>date("Y")||$tyear<(date("Y")-100)){
            $result['isAdult']='成年';
        }elseif($tmonth<0||$tmonth>12){
            $result['isAdult']='成年';
        }elseif($tday<0||$tday>31){
            $result['isAdult']='成年';
        }else{
            $tdate=$tyear."-".$tmonth."-".$tday;
            if((time()-mktime(0,0,0,$tmonth,$tday,$tyear))>18*365*24*60*60){
                $result['isAdult']='成年';
            }else{
                $result['isAdult']='未成年';
            }
        }

        $result['tdate']=$tdate;//生日日期
        return $result;
    }


    /**
     *
     * @param $key
     * @param $data
     * @return array
     */
    public static function array_group($key,$data){
        $re=[];
        foreach ($data as $v){
            foreach ($key as $k_k=>$k_v){//key
                if(array_key_exists($k_k,$v)){
                    foreach ($k_v as $vv){
                        if($vv==$v[$k_k]){
                           $re[$k_k][$vv][]=$v;
                        }
                    }
                }
            }
        }
        return $re;
    }

    /**检测测数组值是否相同
     * @param $array
     * @return bool
     */
    public static function is_alike_array_val($array){
        for($i=0;$i<count($array);$i++){
            if($i==0){
                continue;
            }

            if($array[$i-1]!=$array[$i]){
                return false;
            }
        }
        return true;
    }


    /**
     *多维数组按字段健名排序
     * @param $array
     */
    public static function array_val_sort($array,$feild){
        foreach ($array as $key => $row) {
            $sell_price[$key]  = $row[$feild];
        }
        array_multisort($sell_price, SORT_ASC,$array);
        return $array;
    }

}
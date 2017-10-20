<?php
/**
 * Created by PhpStorm.
 * User: x_l
 * QQ:99628038
 * Date: 2016/12/14
 * Time: 16:34
 */

namespace common\tool;


class str
{
    /**
     * 	作用：格式化参数，签名过程需要使用
     */
    public static function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar='';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * 	作用：生成签名
     */
    public static function getSign($Parameters,$key)
    {
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = http_build_query($Parameters);//改用php原生函数格式化参数
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($String);
        return $result;
    }

    public static function create_sign(){
        return md5(base64_encode(uniqid().'nixinwodon'.time()));
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    public  static function msubstr($str, $start=0, $length, $suffix=false, $charset="utf-8") {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.'...' : $slice;
    }


    /**
     * Formats a JSON string for pretty printing
     *
     * @param string $json The JSON to make pretty
     * @param bool $html Insert nonbreaking spaces and <br />s for tabs and linebreaks
     * @return string The prettified output
     */
    public static function format_json($json, $html = false) {
        $tabcount = 0;
        $result = '';
        $inquote = false;
        $ignorenext = false;
        if ($html) {
            $tab = "   ";
            $newline = "<br/>";
        } else {
            $tab = "\t";
            $newline = "\n";
        }
        for($i = 0; $i < strlen($json); $i++) {
            $char = $json[$i];
            if ($ignorenext) {
                $result .= $char;
                $ignorenext = false;
            } else {
                switch($char) {
                    case '{':
                        $tabcount++;
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '}':
                        $tabcount--;
                        $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                        break;
                    case ',':
                        $result .= $char . $newline . str_repeat($tab, $tabcount);
                        break;
                    case '"':
                        $inquote = !$inquote;
                        $result .= $char;
                        break;
                    case '\\':
                        if ($inquote) $ignorenext = true;
                        $result .= $char;
                        break;
                    default:
                        $result .= $char;
                }
            }
        }
        return $result;
    }


    /**
     * 替换邮箱函数，将邮件名称的替换为*或自定义
     * 返回替换的右键名称
     * 例子：
     * 用户邮箱1234567890@123.com
     * $str='1234567890@123.com';
     * $return=1****67890@123.com
     * case1:echo replaceEmail($str,1,11,'*',true);     返回：1*********@123.com
     * case2:echo replaceEmail($str,1,11,'-',false);    返回：---------0@123.com
     * case3:echo replaceEmail($str,11,11,'/',false);   返回：//////////@123.com
     * case4:echo replaceEmail($str,5,10,'》',true);    返回：12345》》》》》@123.com
     * case5:echo replaceEmail($str,5,11,'+',false);    返回：+++++67890@123.com
     * case6:echo replaceEmail($str,11,2,'%',true);     返回：%%34567890@123.com
     * case7:echo replaceEmail($str,7,6,'8',false);     返回：8884567890@123.com
     * case8:echo replaceEmail($str,7,6,'w',true);      返回：1234567www@123.com
     * @param string $email
     * @param int $start 开始替换的位置，默认为1
     * @param int $len 替换长度，默认为4
     * @param string $char 替换符号，默认为'*'
     * @param bool $left 替换顺序，默认为从左边
     * @return string
     */
    public static function replaceEmail($email, $start = 1, $len = 4, $char = '*', $left = true)
    {
        $emailNameLen = strpos($email, '@');
        $emailaddr = substr($email, $emailNameLen);
        $emailName = substr($email, 0, $emailNameLen);

        if ($len > $emailNameLen) {
            if ($start > $emailNameLen) {
                $start = 0;
                $len = $emailNameLen;
            } else {
                if ($left) {
                    $len = $emailNameLen - $start;
                } else {
                    $len = $emailNameLen - $start;
                    $start = 0;
                }
            }
        } else {
            if ($start > $emailNameLen) {
                if ($left) {
                    $start = 0;
                } else {
                    $start = $emailNameLen - $len;
                }
            } else {
                if ($start + $len > $emailNameLen) {
                    $len = $emailNameLen - $start;
                }
                if ($left) {
                } else {
                    $start = $emailNameLen - $len - $start;
                }
            }
        }
        $replace = '';
        for ($i = 0; $i < $len; $i++) {
            $replace .= $char;
        }
        $emailName = substr_replace($emailName, $replace, $start, $len);
        return $emailName . $emailaddr;
    }

    /**
     * 字符串，将邮件名称的替换为*或自定义
     * 返回替换的右键名称
     * 例子：
     * 用户邮箱1234567890
     * $str='1234567890';
     * $return=1****67890
     * case1:echo replaceString($str,1,11,'*',true);     返回：1*********
     * case2:echo replaceString($str,1,11,'-',false);    返回：---------0
     * case3:echo replaceString($str,11,11,'/',false);   返回：//////////
     * case4:echo replaceString($str,5,10,'》',true);    返回：12345》》》》》
     * case5:echo replaceString($str,5,11,'+',false);    返回：+++++67890
     * case6:echo replaceString($str,11,2,'%',true);     返回：%%34567890
     * case7:echo replaceString($str,7,6,'8',false);     返回：8884567890
     * case8:echo replaceString($str,7,6,'w',true);      返回：1234567www
     * @param string $string
     * @param int $start 开始替换的位置，默认为1
     * @param int $len 替换长度，默认为4
     * @param string $char 替换符号，默认为'*'
     * @param bool $left 替换顺序，默认为从左边
     * @return string
     */
    public static function replaceStr($string, $start = 1, $len = 4, $char = '*', $left = true)
    {
        $stringNameLen = mb_strlen($string);

        if ($len > $stringNameLen) {
            if ($start > $stringNameLen) {
                $start = 0;
                $len = $stringNameLen;
            } else {
                if ($left) {
                    $len = $stringNameLen - $start;
                } else {
                    $len = $stringNameLen - $start;
                    $start = 0;
                }
            }
        } else {
            if ($start > $stringNameLen) {
                if ($left) {
                    $start = 0;
                } else {
                    $start = $stringNameLen - $len;
                }
            } else {
                if ($start + $len > $stringNameLen) {
                    $len = $stringNameLen - $start;
                }
                if ($left) {
                } else {
                    $start = $stringNameLen - $len - $start;
                }
            }
        }
        $replace = '';
        for ($i = 0; $i < $len; $i++) {
            $replace .= $char;
        }
        $stringName = substr_replace($string, $replace, $start, $len);
        return $stringName;
    }

}
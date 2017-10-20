<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2017/9/21
 * Time: 16:30
 */

namespace common\tool;


class TimeLimit
{

    /**
     * 限制时间底线
     * @param $time_str
     * @param $limit_time
     * @param $default
     * @return false|int
     */
    public static function limit_bottom($time_str,$limit_time,$default){
        if($time_str){
            if(is_string($time_str)){
                $time=strtotime($time_str);
                if($time<$limit_time){
                    return $limit_time;
                }else{
                    return $time;
                }
            }else{
                if($time_str<$limit_time){
                    return $limit_time;
                }else{
                    return $time_str;
                }
            }
        }else{
            return $default;
        }
    }

    /**
     * 规范时间值在规定的范围
     * @param array $time_int
     * @param string $range_limit
     * @param int $primary_time
     * @param int $time_bottom
     * @return array
     */
    public static function range_limit(array $time_int,$range_limit, $primary_time=1,$time_bottom){
        $primary_time=in_array($primary_time,[0,1])?$primary_time:1;
        $date_primary_string=$time_int[$primary_time];
        if(is_int($date_primary_string)){
            $date_primary_string=date("Y-m-d H:i:s",$date_primary_string);
        }
        $primary_range=strtotime($date_primary_string.' '.$range_limit);
        if(abs($time_int[1]-$time_int[0])>abs($time_int[$primary_time]-$primary_range)){
            if($primary_range>$time_int[$primary_time]){
                return self::time_bottom([$time_int[$primary_time],$primary_range],$time_bottom);
            }else{
                return self::time_bottom([$primary_range,$time_int[$primary_time]],$time_bottom);
            }
        }
        return self::time_bottom($time_int,$time_bottom);
    }


    public static function time_bottom(array $time_int,$time_bottom){
        foreach ($time_int as $k=>$v){
            if($v<$time_bottom){
                $time_int[$k]=$time_bottom;
            }
        }
        return $time_int;
    }

    /**
     * 转换时间戳为日期时间格式
     * @param array $time_int
     * @param array $range_limit
     * @param int $primary_time
     * @param int $time_bottom
     * @return array
     */
    public static function data_range_time(array $time_int,$range_limit,$primary_time=1,$time_bottom){
        if(is_int($time_int[0])&&is_int($time_int[1])){
            if($time_int[0]>$time_int[1]){
                $time_int=[$time_int[1],$time_int[0]];
            }
        }else{
            $time_int=[strtotime($time_int[0]),strtotime($time_int[1])];
            if($time_int[0]>$time_int[1]){
                $time_int=[$time_int[1],$time_int[0]];
            }
        }
        $time_int=self::range_limit($time_int,$range_limit,$primary_time,$time_bottom);
        return [date('Y-m-d H:i:s',$time_int[0]),date('Y-m-d H:i:s',$time_int[1])];
    }
}
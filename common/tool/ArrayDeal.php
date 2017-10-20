<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2017/9/25
 * Time: 12:08
 */

namespace common\tool;


class ArrayDeal
{

    /**
     *  组合
     * http://blog.csdn.net/yipiankongbai/article/details/8846523
     * @param $a
     * @param $m
     * @return array
     */
    public  static function combination($a, $m) {
        $r = array();
        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }
        for ($i=0; $i<$n; $i++) {
            $t = array($a[$i]);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i+1);
                $c = self::combination($b, $m-1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }
}
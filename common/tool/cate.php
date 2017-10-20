<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2017/7/4
 * Time: 9:36
 */

namespace common\tool;


class cate
{

    /**
     * [array_cate_as_subarray 分类子项，以子数组形式]
     * @param  [array]  $input    [输入]
     * @param  integer $pid     [顶级分类值]
     * @param  string  $pidname [分类对应字段]
     * @param  string  $subname [子数组名]
     * @return [array]           [输出]
     */
    public static function array_cate_as_subarray($input,$pid=0,$pidname='pid',$subname='sub'){
        $uper = [];
        foreach ($input as $k => $v) {
            if ($v[$pidname]==$pid) {
                unset($input[$k]);
                $sub=self::array_cate_as_subarray($input,$v['id'],$pidname,$subname);
                if($sub){
                    $v[$subname] = $sub;
                }
                $uper[]= $v;
            }
        }
        return $uper;
    }

    /**
     * [array_cate_as_subarray_killold 分类子项，以子数组形式]
     * @param  [array]  $input  [输入 传递的是地址]
     * @param  integer $pid     [顶级分类值]
     * @param  string  $pidname [分类对应字段]
     * @param  string  $subname [子数组名]
     * @return [array]          [输出]
     */
    public static function array_cate_as_subarray_killold(&$input,$pid=0,$pidname='pid',$subname='sub'){
        $uper = [];
        foreach ($input as $k => $v) {
            if ($v[$pidname]==$pid) {
                unset($input[$k]);
                $sub=self::array_cate_as_subarray_killold($input,$v['id'],$pidname,$subname);
                if($sub){
                    $v[$subname] = $sub;
                }
                $uper[]= $v;
            }
        }
        return $uper;
    }


    /**
     * [array_cate_as_sequence 分类子类，以顺序数组形式]
     * @param  [array]  $input      [输入]
     * @param  [array]  &$output    [输出，地址，必须先定义输出]
     * @param  integer $pid         [顶级分类值]
     * @param  string  $pidname     [分类对应字段]
     * @return [array]              [输出，可以写也可以不写]
     */
    public static function array_cate_as_sequence($input,&$output,$pid=0,$pidname='pid'){

        foreach ($input as $k => $v) {
            if ($v[$pidname]==$pid){
                unset($input[$k]);
                $output[]= $v;
                self::array_cate_as_sequence($input,$output,$v['id'],$pidname);
            }
        }
        return $output;
    }


    /**
     * [array_direct_father 获取直接父级别]
     * @param  [type] $input   [输入级别]
     * @param  [type] $total   [分类好的数组]
     * @param  string $pidname [pid名]
     * @param  string $subname [子分类名]
     * @return [type]          [返回,未找到返回false]
     */
    public static function array_direct_father($input,$total,$pidname='pid',$subname='sub'){
        if($input==0){
            return false;
        }
        foreach ($total as $key => $value) {
            if($value['id']==$input){
                if(isset($value[$subname])&&$value[$subname]){
                    unset($value[$subname]);
                }
                return $value;
            }else{
                if(isset($value[$subname])&&$value[$subname]){
                    $result =self::array_direct_father($input,$value[$subname],$pidname,$subname);
                    if(is_array($result) && $result){
                        return $result;
                    }
                }
            }
        }
        return false;
    }

    /**
     * [array_all_father 获取最上级的父级信息]
     * @param  [int] $input   [输入]
     * @param  [array] $total   [总分类]
     * @param  string $pidname [pid名]
     * @param  string $subname [子分类名]
     * @return [type]          [description]
     */
    public static function array_top_father($input,$total,$pidname='pid',$subname='sub'){
        if($input==0){
            return false;
        }
        foreach ($total as $key => $value) {
            if($value['id']==$input){
                if(isset($value[$subname])&&$value[$subname]){
                    unset($value[$subname]);
                }
                return $value;
            }else{
                if(isset($value[$subname])&&$value[$subname]){
                    $result =self::array_top_father($input,$value[$subname],$pidname,$subname);
                    if(is_array($result) && $result){
                        unset($value[$subname]);
                        return $value;
                    }
                }
            }
        }
        return false;
    }

    /**
     * [array_all_father 获取所有父级信息]
     * @param  [type] $input   [输入的儿子的pid]
     * @param  [type] $total   [总分类]
     * @param  [type] $sun     [子分类，如果需要插入子分类的话]
     * @param  string $pidname [pid名]
     * @param  string $subname [子分类名]
     * @return [type]          [description]
     */
    public static function array_all_father($input,$total,$sun=[],$pidname='pid',$subname='sub'){
        if($input==0){
            return false;
        }
        foreach ($total as $key => $value) {
            if($value['id']==$input){
                if($sun){
                    $value[$subname]=$sun;
                }
                return $value;
            }else{
                if(isset($value[$subname])&&$value[$subname]){
                    $result = self::array_all_father($input,$value[$subname],$sun,$pidname,$subname);
                    if(is_array($result) && $result){
                        $value[$subname]=[$result];
                        return $value;
                    }
                }
            }
        }
        return false;
    }

    /**
 * [array_cate_in_sub 获取input的中对应total的所有父级信息,并组织好结构]
 * @param  [type] $input   [输入的儿子的pid]
 * @param  [type] $total   [总分类]
 * @param  string $pidname [pid名]
 * @param  string $subname [子分类名]
 * @return [type]          [返回值]
 */
    public static function array_cate_in_sub($input,$total,$pid=0,$pidname='pid',$subname='sub'){

        $result=self::array_cate_as_subarray_killold($input,$pid,$pidname,$subname);
        $total =self::array_cate_as_subarray($total,$pid,$pidname,$subname);

        if($input){
            $pid_input=[];
            $sub=[];
            foreach($input as $key=>$value){
                if(in_array($value[$pidname], $pid_input)){
                    $sub[$value[$pidname]][]=$value;
                }else{
                    $pid_input[]=$value[$pidname];
                    $sub[$value[$pidname]][]=$value;
                }
            }
            foreach ($sub as $key => $value) {

                $result[]=self::array_all_father($key,$total,$value,$pidname,$subname);
            }
        }
        return self::array_cate_merge_father($result,$subname);
    }

    /**
     * [array_cate_in_sub 获取input的中对应total的最顶级父级信息,并组织好结构]
     * @param  [type] $input   [输入]
     * @param  [type] $total   [总分类]
     * @param  string $pidname [pid名]
     * @param  string $subname [子分类名]
     * @return [type]          [返回值]
     */
    public static function array_cate_only_top($input,$total,$pidname='pid',$subname='sub'){
        $total=\common\tool\cate::array_cate_as_subarray($total);
        $father_pool=[];
        foreach ($input as $k=>$v){
            $father=self::array_top_father($v['pid'],$total,$pidname,$subname);
            if(array_key_exists($father['id'],$father_pool)){
                $father_pool[$father['id']]['sub'][]=$v;
            }else{
                $father_pool[$father['id']]=$father;
                $father_pool[$father['id']]['sub'][]=$v;
            }
        }
        return $father_pool;
    }


    /**
     * [array_cate_merge_father 合并同级是相同复级的子级]
     * @param array $input [输入，根据同级的id来判断]
     * @param string $subname
     * @return mixed
     */
    public static function array_cate_merge_father($input,$subname='sub'){
        $idpool=[];
        foreach ($input as $key => $value) {
            if(isset($value['id'])){
                if(array_key_exists($value['id'],$idpool)&&$idpool){
                    if(isset($value[$subname])&&$value[$subname]) {
                        $sub=array_merge($input[$idpool[$value['id']]][$subname],$value[$subname]);
                        unset($input[$key]);
                        $input[$idpool[$value['id']]][$subname]=self::array_cate_merge_father($sub,$subname='sub');
                    }
                }else{
                    $idpool[$value['id']]=$key;
                }
            }
        }
        return $input;
    }



}
<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2017/6/14
 * Time: 16:36
 * 专门用于处理文章字符替换
 * author : orx
 */

namespace common\tool;


class article
{

    /**
     * 更改分类
     * 举个例子：
     * $data = [['label_id'=>"1,2,3",],['label_id'=>"4,2,5",],];
     * $key = 'label_id';
     *
     * $cate = [1=>'a',2=>'b','3'=>'c','4'=>'d','5'=>'e'];
     * 返回
     * $data = [['label_id'=>"abc",],['label_id'=>"dbe",],];
     *
     * @param array $data
     * @param array $cate
     * @param string $key
     * @return array mixed
     */
    public static function changeName($data,$cate,$key){
        foreach ($data as $keyData => $valueData) {
            //----栏目名字更换----
            foreach ($cate as $cateData) {
                if (($cateData['id'] == $valueData[$key]) && $cateData['type'] == 0) {  //栏目的type为0
                    $data[$keyData][$key] = $cateData['name'];
                }
            }
            //----给标签名字更换----
            $str = '';
            foreach (array_filter(preg_split("/[^0-9]/", $valueData['label_id'])) as $vLabel_id) {
                foreach ($cate as $cateData) {
                    if ($cateData['id'] == $vLabel_id && $cateData['type'] == 1) {  //标签的type为1
                        $str .= $cateData['name'] . '，';
                    }
                }
            }
            $data[$keyData]['label_id'] = mb_substr($str, 0, (mb_strlen($str) - 1));     //去除最后一个逗号
        }
        return $data;
    }

    /**
     * 取出第一张图片
     * @param array $data
     * @return array
     */
    public static function getFirstImgFromContent(array $data)
    {
        //取出图片路径
        foreach ($data as $k => $v) {
            preg_match('/src="(.*?)"/', $v['content'], $img);
            if ($img) {
                $data[$k]['img'] = $img[1];
            } else {
                $data[$k]['img'] = '';
            }
        }
        return $data;
    }

    public static function getFirstImgFromImages(array $data)
    {
        //取出图片路径
        foreach ($data as $k => $v) {
            $images = explode(',',$v['images']);
            if ($images[0]) {
                $data[$k]['img'] = $images[0];
            } else {
                $data[$k]['img'] = '';
            }
        }
        return $data;
    }

    //去除html标记并截取文字前40个字，content内容仅保留纯文本
    public static function wordFilter(array $data,$num)
    {
        foreach ($data as $k => $v) {
            $data[$k]['content'] = mb_substr(strip_tags($data[$k]['content']), 0, $num);
        }
        return $data;
    }

    /*
     * 标签更换名称
     * 原本标签为代号，现更换为中名称
     * $data输入的数据
     * $cate对应的标签
     */
    public static function labelFilter($data, $cate)
    {
        foreach ($data as $keyData => $valueData) {
            $str = '';
            foreach (array_filter(preg_split("/[^0-9]/", $valueData['label_id'])) as $vLabel_id) {
                foreach ($cate as $cateData) {
                    if ($cateData['id'] == $vLabel_id) {
                        $str .= $cateData['name'] . '，';
                    }
                }
            }
            $data[$keyData]['label_id'] = mb_substr($str, 0, (mb_strlen($str) - 1));//去除最后一个逗号
        }
        return $data;
    }

    /*
     * 返回标签数组
     * $data所处理的数据
     * $cate标签分类
     */
    public static function labelUnique($data, $cate)
    {
        $strLabel = '';          //记录label标签，记录所有的标签
        foreach ($data as $keyData => $valueData) {
            $strLabel .= $valueData['label_id'] . ',';
        }
        $label_num = array_flip(array_flip(array_filter(preg_split("/[^0-9]/", $strLabel))));
        $label = [];
        foreach ($cate as $v) {
            if (in_array($v['id'], $label_num)) {
                $label [] = $v;
            }
        }
        return $label;
    }


}
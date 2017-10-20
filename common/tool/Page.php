<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2017/9/21
 * Time: 9:39
 */

namespace common\tool;


use yii\data\Pagination;

class Page  extends Pagination
{
    const PAGEPART=['show_page_count','show_page_center','show_page_jump','show_page_size'];//定义分页的组成部分，可以定制分页显示的组成
    public $pagePart=self::PAGEPART;

    public function setPagePart($part=[]){
        $this->pagePart=$part&&array_diff($part, self::PAGEPART)?array_diff($part, self::PAGEPART): self::PAGEPART;
    }

    public function getPagePart(){
        return $this->pagePart?$this->pagePart:self::PAGEPART;
    }
}
<?php
/**
 * 手机API工具包
 * User: admin
 * Date: 2016/9/22
 * Time: 15:07
 */

namespace common\tool;
use yii\db\Query;

class mobile
{
    public static $use_mothod;
    const codespace='app.tool.mobile';
    const code=[
        'gui_su_di'=> [
            1=>'手机号码不符合规范',
            2=>'查询号码归属地失败',
            3=>'查询固话归属地失败',
        ]
    ];
    /**
     * 查询手机归属地
     * @param $on
     * @return array|bool|mixed
     */
    public static function gui_su_di($on){
        $codes=ApiCode::init_code(self::codespace,'gui_su_di',self::code);//初始化code
        if(preg_match('/^(010|02\d{1}|0[3-9]\d{2})\d{7,9}$/',$on)){//固话
            $data=(new Query())->from('{{%icp_tel}}')->where(['phone'=>$on])->limit(1)->one();
            if(!$data){
                return  $codes[3];
            }
            $content=$data['province'].$data['city'].$data['isp'];
            return  ['status'=>true,'content'=>$content,'senfen'=>$data['senfen_code'],'city'=>$data['area_code'],
                'senfen_name'=>$data['province'],'city_name'=>$data['city'],
                'icp'=>$data['isp']];

        }else{//手机号码
            if (strlen($on)!=11){
                return  $codes[1];
            }
            $data=(new Query())->from('{{%icp}}')->where(['phone'=>substr($on,0,7)])->limit(1)->one();
            if(!$data){
                return  $codes[2];
            }
            $content=$data['province'].$data['city'].$data['isp'];
            if($data['if_mvno']==1){
                $data['isp']='虚商';
            }
            return  ['status'=>true,'content'=>$content,'senfen'=>$data['senfen_code'],'city'=>$data['area_code'],
                'senfen_name'=>$data['province'],'city_name'=>$data['city'],
                'icp'=>$data['isp'],'if_mvno'=>$data['if_mvno'],'mvno'=>$data['mvno'],'mvno_icp'=>$data['mvno_isp']];
        }
    }







}
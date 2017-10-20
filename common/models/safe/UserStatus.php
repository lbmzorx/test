<?php
/**
 * Created by PhpStorm.
 * User: 刘超富
 * QQ: 251651698
 * Date: 2017/8/21
 * Time: 21:28
 */
namespace common\models\safe;
use common\models\common\SystemConfig;
use common\models\common\user\Users;

class UserStatus{
    public static $user_status=[0=>'未知异常',1=>'正常',2=>'被冻结'];
    public static $open_user_status=[1=>'正常',2=>'待审核',3=>'被冻结',4=>'被清退'];
    public static $sell_user_status=[1=>'正常',2=>'待审核',3=>'被冻结',4=>'被清退'];
    public static $agent_user_status=[1=>'正常',2=>'待审核',3=>'被冻结',4=>'被清退'];
    public static function inspect_user_status($user_id,$pintai_num=''){
        $user=Users::find()->where(['id'=>$user_id])->one();
        if(!$user){
            return ['status'=>false,'msg'=>'用户不存在'];
        }
        if($user['status']!=1){
            return ['status'=>false,'msg'=>'用户基础状态异常'];
        }
        if($pintai_num){
            $pintai_user_model= SystemConfig::getPintaiUserModelName($pintai_num);
            $pintai_user=$pintai_user_model::find()->where(['user_id'=>$user_id])->one();
            if(!$pintai_user){
                return ['status'=>false,'msg'=>'平台用户不存在'];
            }
            if($pintai_user['status']!=1){
                return ['status'=>false,'msg'=>'平台用户状态异常'];
            }
            if(Users::inspect_user_info($user_id,$user['type'])!=1){
                return ['status'=>false,'msg'=>'用户身份信息异常'];
            }
            return ['status'=>true,'user'=>$user,'pintai_user'=>$pintai_user];
        }
        return ['status'=>true,'user'=>$user];
    }
}
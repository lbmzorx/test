<?php

namespace  common\models\safe;

use Yii;

class SiminVerify extends \common\models\datamodels\SiminVerify
{

    public static $verify=['0'=>'未认证','1'=>'待审核','2'=>'审核中','3'=>'已认证','4'=>'未通过审核'];
    public static $verify_img=['0'=>'待审核','1'=>'已通过审核','2'=>'未通过审核',];


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'sex', 'cred_number', 'img_zen', 'img_fan', 'img_souci', 'long_time', 'address'], 'required'],
            [['user_id', 'sex', 'add_time', 'edit_time', 'expiry_time_start', 'expiry_time_end', 'long_time', 'verify', 'img_cred_verify', 'img_zen_verify', 'img_fan_verify', 'img_souci_verify'], 'integer'],
            [['orther_msg'], 'string'],
            [['name', 'cred_number'], 'string', 'max' => 50],
            [['img_cred', 'img_zen', 'img_fan', 'img_souci', 'address'], 'string', 'max' => 255],
        ];
    }
}

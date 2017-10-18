<?php

namespace common\models\admin;

use Yii;

/**
 * This is the model class for table "{{%admins}}".
 *
 * @property string $id
 * @property string $employer_id
 * @property string $username
 * @property string $auth_key
 * @property string $passwd
 * @property string $resetpasswd
 * @property string $phone
 * @property string $email
 * @property string $qq
 * @property integer $role_id
 * @property string $head_thum
 * @property string $head
 * @property integer $delete_status
 * @property integer $audit_status
 * @property integer $add_time
 * @property integer $edit_time
 */
class Admins extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admins}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['employer_id', 'username', 'auth_key'], 'required'],
            [['role_id', 'delete_status', 'audit_status', 'add_time', 'edit_time'], 'integer'],
            [['employer_id'], 'string', 'max' => 20],
            [['username'], 'string', 'max' => 50],
            [['auth_key'], 'string', 'max' => 32],
            [['passwd', 'resetpasswd', 'qq', 'head_thum', 'head'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 18],
            [['email'], 'string', 'max' => 60],
            [['employer_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employer_id' => '员工号码',
            'username' => '用户名',
            'auth_key' => '自动登录key',
            'passwd' => 'Passwd',
            'resetpasswd' => 'Resetpasswd',
            'phone' => 'Phone',
            'email' => 'Email',
            'qq' => 'Qq',
            'role_id' => 'Role ID',
            'head_thum' => 'Head Thum',
            'head' => 'Head',
            'delete_status' => '帐号状态，0已经删除，1帐号冻结，2未激活,10状态活跃',
            'audit_status' => '审核状态，0未审核，1已经审核',
            'add_time' => 'Add Time',
            'edit_time' => 'Edit Time',
        ];
    }
}

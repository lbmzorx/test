<?php
namespace common\models\admin\user;

use Yii;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
/**
 * This is the model class for table "{{%admins}}".
 */
class Admins extends \common\models\admin\Admins implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * 根据id找帐号
     * @param int|string $id
     * @return static
     */
    public static function findIdentity($id){
        return static::findOne(['id' => $id, 'delete_status' => self::STATUS_ACTIVE]);
    }

    /**
     * 根据用户名查找
     * @param $username
     * @return static
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'delete_status' => self::STATUS_ACTIVE]);
    }

    /**
     * 通过口令找身份
     * @param mixed $token
     * @param null $type
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null){
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * 找用户id
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId(){
        return $this->id;
    }

    /**
     * 创建自动登录密钥
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     *  获取自动登录密钥
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey(){
        return $this->auth_key;
    }

    /**
     * 验证自动登录密钥
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey){
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 验证密码
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwd);
    }

    /**
     * 设置密码
     * @param $password
     */
    public function setPassword($password)
    {
        $this->passwd = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * 创建重设密码口令
     */
    public function generatePasswordResetToken()
    {
        $this->resetpasswd = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * 删除密码重设口令
     */
    public function removePasswordResetToken()
    {
        $this->resetpasswd = null;
    }
}

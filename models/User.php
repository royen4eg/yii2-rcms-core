<?php

namespace rcms\core\models;

use rcms\core\base\ActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 * @author Andrii Borodin
 * @since 0.1
 *
 * @property int $user_id
 * @property string $username
 * @property string $password
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $auth_key
 * @property string $email
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password_hash', 'auth_key', 'email',], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username'], 'string', 'max' => 32],
            [['password_hash', 'auth_key'], 'string', 'max' => 64],
            [['password_reset_token', 'email'], 'string', 'max' => 128],
            [['username'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['auth_key'], 'unique'],
            [['email'], 'unique'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('rcms-core', 'User ID'),
            'username' => Yii::t('rcms-core', 'Username'),
            'password_hash' => Yii::t('rcms-core', 'Password Hash'),
            'password_reset_token' => Yii::t('rcms-core', 'Password Reset Token'),
            'auth_key' => Yii::t('rcms-core', 'Auth Key'),
            'email' => Yii::t('rcms-core', 'Email'),
            'status' => Yii::t('rcms-core', 'Status'),
            'created_at' => Yii::t('rcms-core', 'Created At'),
            'updated_at' => Yii::t('rcms-core', 'Updated At'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritDoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['user_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds an identity by the given username.
     * @param string|int $username the username to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled)
     */
    public static function findIdentityByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @param $username
     * @return IdentityInterface|null
     */
    public static function findByUsername($username)
    {
        return static::findIdentityByUsername($username);
    }

    /**
     * Finds an identity by the given email.
     * @param string $email the email to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled)
     */
    public static function findIdentityByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @param $email
     * @return IdentityInterface|null
     */
    public static function findByEmail($email)
    {
        return static::findIdentityByEmail($email);
    }

    /**
     * Finds an identity by the given username or email.
     * @param string $ue the username or the email to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled)
     */
    public static function findIdentityByUsernameOrEmail($ue)
    {
        return static::find()->active()->where([
            'OR', ['username' => $ue], ['email' => $ue],
        ])->one();
    }

    /**
     * This method will ignore user status. You may get deactivated users with token
     * @inheritDoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritDoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritDoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     * @throws \yii\base\Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     * @throws \yii\base\Exception
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
}

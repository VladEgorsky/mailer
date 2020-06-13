<?php

namespace app\components;

use yii\base\NotSupportedException;

/**
 * Класс-заглушка для site/login
 *
 * Class User
 * @package app\components
 */
class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id = 1;
    public $authKey = 'test-admin-authkey';

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        throw new NotSupportedException();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return false;
    }

}

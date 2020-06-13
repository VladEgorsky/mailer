<?php

namespace app\models;

use app\components\User;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $_user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $providerId;
    public $email;
    public $password;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['providerId', 'email', 'password'], 'required'],
            ['providerId', 'integer'],
            ['providerId', 'in', 'range' => Provider::getIds()],
            [['email', 'password'], 'string'],
            [['email', 'password'], 'trim'],
            ['email', 'email'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'providerId' => 'Провайдер',
            'email' => 'EMail',
            'password' => 'Пароль',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
//        if (!$this->hasErrors()) {
//            $user = $this->getUser();
//
//            if (!$user || !$user->validatePassword($this->password)) {
//                $this->addError($attribute, 'Incorrect email or password.');
//            }
//        }
    }

    /**
     * Logs in a user using the provided email and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if (!$this->validate()) {
            return false;
        }

        return Yii::$app->user->login(User::findIdentity(100), 3600);
    }
}

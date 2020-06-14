<?php

namespace app\models;

use app\components\Mailer;
use app\components\User;
use Yii;
use yii\base\Model;

/**
 * Class LoginForm is the model behind the login form.
 * @package app\models
 */
class LoginForm extends Model
{
    public $providerId;
    public $email;
    public $password;

    /**
     * {@inheritdoc}
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
            ['password', 'validateCredentials'],
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
     * Выбираем данные с почтового сервера и сохраняем их в
     * Yii::$app->mailer->mailbox[header] & mailbox[data]
     */
    public function validateCredentials()
    {
        /** @var Mailer $mailer */
        $mailer = Yii::$app->mailer;
        $credentials = (object) [
            'imapServer' => Provider::getImapServer($this->providerId),
            'email' => $this->email,
            'password' => $this->password,
        ];

        $mailer->setCredentials($credentials);
        $response = $mailer->getDataFromServer();

        if (!empty($response['errorMessage'])) {
            $this->addError('password', $response['errorMessage']);
        }
    }

    /**
     * Logs in a user using the provided credentials
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        return Yii::$app->user->login(User::findIdentity(1));
    }
}

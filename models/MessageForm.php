<?php

namespace app\models;

use app\components\Mailer;
use Yii;
use yii\base\Model;

class MessageForm extends Model
{
    public $from;
    public $to;
    public $subject;
    public $body;
    public $files;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['from', 'to', 'subject', 'body'], 'required'],
            [['from', 'to', 'subject', 'body'], 'string'],
            [['from', 'to', 'subject', 'body'], 'trim'],
            ['files', 'each', 'rule' => ['file'], 'skipOnEmpty' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'from' => 'От кого (email)',
            'to' => 'Кому (email)',
            'subject' => 'Тема',
            'body' => 'Текст сообщения',
        ];
    }

    /**
     * @return bool
     */
    public function send()
    {
        // Выбираем email из "Name <mailbox@server.com>"
        $to = $this->to;
        $pattern = '#\<(.+)\>#';

        if (preg_match($pattern, $to, $matches)) {
            $to = trim($matches[1]);
        }

        $message = Yii::$app->mailer->compose()
            ->setFrom($this->from)
            ->setTo($to)
            ->setSubject($this->subject)
            ->setHtmlBody($this->body);

        foreach ($this->files as $file) {
            $message->attach($file->tempName, ['fileName' => $file->name]);
        }

        Yii::$app->mailer->setTransport($this->getMailerSmtpTransport());
        return $message->send();
    }

    /**
     * @return array
     */
    protected function getMailerSmtpTransport()
    {
        /** @var Mailer $mailer */
        $mailer = Yii::$app->mailer;

        return [
            'class' => 'Swift_SmtpTransport',
            'host' => $mailer->getCredentials()->smtpServer,
            'username' => $mailer->getCredentials()->email,
            'password' => $mailer->getCredentials()->password,
            'port' => '465',
            'encryption' => 'ssl',
        ];
    }
}

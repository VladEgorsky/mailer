<?php

namespace app\components;

use app\models\LoginForm;
use app\models\Provider;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox as ImapMailbox;
use Yii;

/**
 * Выбираем данные почтового ящика с сервера и сохраняем их в св-вах этого класса
 * $this->mailBox[header] & $this->mailBox[data]
 *
 * СТРУКТУРА  ОБЪЕКТА  MAILBOX[HEADER]:
 *  Date - current system time formatted according to RFC2822
 *  Driver - protocol used to access this mailbox: POP3, IMAP, NNTP
 *  Mailbox - the mailbox name
 *  Nmsgs - number of mails in the mailbox
 *  Recent - number of recent mails in the mailbox
 *
 * СТРУКТУРА  ЭЛЕМЕНТОВ  МАССИВА  MAILBOX[DATA]:
 *  subject - the mails subject
 *  from - who sent it
 *  sender - who sent it
 *  to - recipient
 *  date - when was it sent
 *  message_id - Mail-ID
 *  references - is a reference to this mail id
 *  in_reply_to - is a reply to this mail id
 *  size - size in bytes
 *  uid - UID the mail has in the mailbox
 *  msgno - mail sequence number in the mailbox
 *  recent - this mail is flagged as recent
 *  flagged - this mail is flagged
 *  answered - this mail is flagged as answered
 *  deleted - this mail is flagged for deletion
 *  seen - this mail is flagged as already read
 *  draft - this mail is flagged as being a draft
 *  hasAttachments - есть ли вложения (bool)
 *
 * Class Mailer
 * @package app\components
 */
class Mailer extends \yii\swiftmailer\Mailer
{
    /**
     * Здесь хранятся данные для функции imap_open
     * imapServer, email, password
     *
     * @var \stdClass
     */
    protected $credentials;

    /**
     * Ресурс создаваемый функцией imap_open
     *
     * @var resource
     */
    protected $imapResource;

    /**
     * Каталог в папке @webroot для хранения вложенных файлов
     * @var string
     */
    protected $attachmentsDir = DIRECTORY_SEPARATOR . 'uploads';

    /**
     * Здесь будем хранить выбранные данные для почтового ящика
     * Header: [from, message_num, ... ]
     * Data: [
     *      [subject, from, reply_to, flagged, has_attachment, date ... ],
     *      [subject, from, reply_to, flagged, has_attachment, date ... ]
     * ]
     *
     * @var array
     */
    protected $mailBox = [
        'header' => [],
        'data' => [],
    ];


    /*******************************************************************************
     *                              Getters & Setters
     *
     * @param \stdClass $odj
     */
    public function setCredentials(\stdClass $odj)
    {
        $this->credentials = $odj;
    }

    /**
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    protected function setImapResource()
    {
        $c = $this->credentials;

        $this->imapResource = new ImapMailbox(
            $c->imapServer,                         // IMAP server and mailbox folder
            $c->email,                              // Username for the before configured mailbox
            $c->password,                           // Password for the before configured username
            $this->getAttachmentsDir($c->email),    // Directory, where attachments will be saved (optional)
            'UTF-8'                    // Server encoding (optional)
        );
    }

    /**
     * @return resource
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    protected function getImapResource()
    {
        if (!$this->imapResource) {
            $this->setImapResource();
        }

        return $this->imapResource;
    }

    /**
     * @param string $email
     * @param bool $createIfNotExist
     * @return string
     */
    protected function getAttachmentsDir(string $email, bool $createIfNotExist = true)
    {
        $dir = Yii::getAlias('@webroot') . $this->attachmentsDir . DIRECTORY_SEPARATOR . $email;

        if ($createIfNotExist && !is_dir($dir)) {
            mkdir($dir);
        }

        return $dir;
    }


    /*******************************************************************************
     *                               Public functions
     *
     * @return array|bool[]
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    public function getDataFromServer()
    {
        try {
            /** @var ImapMailbox $resource */
            $resource = $this->getImapResource();
            $resource->setAttachmentsIgnore(true);

            // Date, Driver, Mailbox, Nmsgs, Recent
            $this->mailBox['header'] = $resource->checkMailBox();

            // Ids всех почтовых сообщений в ящике
            // http://php.net/manual/en/function.imap-search.php
            $mailIds = $resource->searchMailbox('ALL');

            // Данные для mailBox['data]
            $data = $resource->getMailsInfo($mailIds);
            $this->setMailboxData($data);

        } catch(ConnectionException $e) {
            return [
                'result' => false,
                'errorMessage' => $e->getMessage(),
            ];
        }

        return ['result' => true];
    }

    /**
     * Выбираем тело сообщения
     *
     * @param int $uid
     * @return string
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    public function getMailBody(int $uid): string
    {
        /** @var ImapMailbox $resource */
        $resource = $this->getImapResource();

        return $resource->getRawMail($uid);
    }

    /**
     * @return array|mixed
     */
    public function getMailboxHeader()
    {
        return $this->mailBox['header'];
    }

    /**
     * @return array|mixed
     */
    public function getMailboxData()
    {
        return $this->mailBox['data'];
    }


    /*******************************************************************************
     *                               Protected functions
     * В параметре принимаем массив объектов.
     * В $this->mailBox['data'] конвертируем его в массив массивов.
     * Дополнительно выбираем инфу о наличии в сообщении вложений
     *
     * @param array $data
     * @return bool
     */
    protected function setMailboxData(array $data): bool
    {
        foreach ($data as $obj) {
            $o = $this->getImapResource()->getMail($obj->uid);

            // Получаем значение скрытого св-ва $o->hasAttachments
            $rc = new \ReflectionClass($o);
            $prop = $rc->getProperty('hasAttachments');
            $prop->setAccessible(true);

            $arr = (array) $obj;
            $arr['hasAttachments'] = $prop->getValue($o);
            $this->mailBox['data'][$obj->uid] = $arr;
        }

        return true;
    }
}

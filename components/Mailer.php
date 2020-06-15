<?php

namespace app\components;

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
     * объект new ImapMailbox()
     *
     * @var object
     */
    protected $imapObject;

    /**
     * Каталог в папке @webroot для хранения вложенных файлов
     * @var string
     */
    protected $attachmentsFolder = 'attachments';

    /**
     * Здесь хранятся данные для функции imap_open
     * imapServer, email, password
     *
     * @var \StdClass
     */
    protected $credentials;

    /**
     * Данные для почтового ящика: {from, message_num, ... }
     *
     * @var \StdClass
     */
    protected $mailboxHeader;

    /**
     * Данные для каждого сообщения в почтовом ящике: [
     *      [subject, from, reply_to, flagged, has_attachment, date ... ],
     *      [subject, from, reply_to, flagged, has_attachment, date ... ]
     * ]
     * Ввиде массива чтобы использовать для ArrayDataProvider
     *
     * @var array
     */
    protected $mailboxData;

    /**
     * Mailer constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $session = Yii::$app->session;
        $this->credentials = $session->get('mailboxCredentials');
        $this->mailboxHeader = $session->get('mailboxHeader');
        $this->mailboxData = $session->get('mailboxData');

        parent::__construct($config);
    }


    /*******************************************************************************
     *                              Getters & Setters
     *
     * @param \StdClass $obj
     */
    public function setCredentials(\StdClass $obj)
    {
        $this->credentials = $obj;
        $this->mailboxHeader = $this->mailboxData = null;
    }

    /**
     * @return \StdClass|null
     */
    public function getCredentials()
    {
        return $this->credentials ?? Yii::$app->session->get('mailboxCredentials');
    }

    /**
     * @param int $messageId
     * @param string|null $email
     * @return string
     */
    protected function getAttachmentsDir(int $messageId, string $email = null): string
    {
        if (!$email) {
            $email = $this->credentials->email;
        }

        $dir = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $this->attachmentsFolder;
        $dir .= DIRECTORY_SEPARATOR . $email . '-' . $messageId;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    /**
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    protected function setImapObject()
    {
        $c = $this->getCredentials();

        $this->imapObject = new ImapMailbox(
            $c->imapServer,             // IMAP server and mailbox folder
            $c->email,                  // Username for the before configured mailbox
            $c->password,               // Password for the before configured username
            null           // Directory, where attachments will be saved (optional)
        );
    }

    /**
     * @return object
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    protected function getImapObject()
    {
        if (!$this->imapObject) {
            $this->setImapObject();
        }

        return $this->imapObject;
    }

    /**
     * @return \stdClass|null
     */
    public function getMailboxHeader(): \stdClass
    {
        return $this->mailboxHeader ?? Yii::$app->session->get('mailboxHeader');
    }

    /**
     * @param int|null $id
     * @return array|mixed|null
     */
    public function getMailboxData(int $id = null)
    {
        $data = $this->mailboxData ?? Yii::$app->session->get('mailboxData');

        if (!$data) {
            return null;
        }

        return $id ? $data[$id] : $data;
    }


    /*******************************************************************************
     *                               Public functions
     * Выбираем данные для $this->>mailboxHeader & mailboxData и сохраняем их в сессию
     *
     * @return array|bool[]
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    public function getDataFromServer()
    {
        try {
            /** @var ImapMailbox $imapObj */
            $imapObj = $this->getImapObject();
            $imapObj->setAttachmentsIgnore(true);

            // Date, Driver, Mailbox, Nmsgs, Recent
            $this->mailboxHeader = $imapObj->checkMailBox();

            // Ids всех почтовых сообщений в ящике
            // http://php.net/manual/en/function.imap-search.php
            $mailIds = $imapObj->searchMailbox('ALL');

            // Данные для mailBox['data]
            $data = $imapObj->getMailsInfo($mailIds);
            $this->setMailboxData($data);

            // Сохраняем данные в сессию
            $this->storeDataToSession();

        } catch (ConnectionException $e) {
            return ['errorMessage' => 'IMAP connection failed: '.$e->getMessage()];
        } catch (\Exception $e) {
            return ['errorMessage' => 'An error occured: '.$e->getMessage()];
        }

        return ['result' => true];
    }

    /**
     * Выбираем тело сообщения
     *
     * @param int $id
     * @return mixed|string
     */
    public function getMessageBody(int $id)
    {
        $body = Yii::$app->session->get('body_' . $id);
        if ($body) {
            return $body;
        }

        try {
            /** @var ImapMailbox $imapObj */
            $imapObj = $this->getimapObject();
            $data = $imapObj->getMail($id);
            $attachments = $data->getAttachments();

            $dir = $this->getAttachmentsDir($id);
            foreach ($attachments as $attachment) {
                $attachment->setFilePath($dir . DIRECTORY_SEPARATOR . $attachment->name);
                $attachment->saveToDisk();
            }

            $body = $data->textHtml ?? $data->textPlain;
            Yii::$app->session->set('body_' . $id, $body);

            return $body;

        } catch (ConnectionException $e) {
            die('IMAP connection failed: '.$e->getMessage());
        } catch (\Exception $e) {
            die('An error occured: '.$e->getMessage());
        }
    }

    /**
     * @param int $messageId
     * @param string|null $email
     * @return array|false
     */
    public function getAttachedFiles(int $messageId, string $email = null)
    {
        $dir = $this->getAttachmentsDir($messageId, $email);
        return glob($dir . DIRECTORY_SEPARATOR . '*.*');
    }

    /*******************************************************************************
     *                               Protected functions
     *
     * В параметре принимаем массив объектов.
     * В $this->mailBox['data'] конвертируем его в массив массивов.
     * Дополнительно выбираем инфу о наличии в сообщении вложений
     *
     * @param array $data
     * @return bool
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    protected function setMailboxData(array $data): bool
    {
        foreach ($data as $obj) {
            $arr = (array) $obj;
            $o = $this->getimapObject()->getMail($obj->uid, false);
            $arr['hasAttachments'] = $o->hasAttachments();
            $arr['date'] = strtotime($arr['date']);

            $this->mailboxData[$obj->uid] = $arr;
        }

        return true;
    }

    /**
     * Сохраняем данные в сессию.
     */
    protected function storeDataToSession()
    {
        $session = Yii::$app->session;

        $session->set('mailboxCredentials', $this->credentials);
        $session->set('mailboxHeader', $this->mailboxHeader);
        $session->set('mailboxData', $this->mailboxData);
    }
}

<?php

namespace app\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

class Provider extends Model
{
    const GMAIL_COM = 10;
    const MAIL_RU = 20;
    const YANDEX_RU = 30;

    /**
     * @return array
     */
    const LIST = [
        self::GMAIL_COM => [
            'id' => self::GMAIL_COM,
            'name' => 'Gmail.com',
            'server' => 'gmail.com',
            'imapServer' => '{imap.gmail.com:993/imap/ssl/novalidate-cert}',
        ],
        self::MAIL_RU => [
            'id' => self::MAIL_RU,
            'name' => 'Mail.ru',
            'server' => 'mail.ru',
            'imapServer' => '{imap.mail.ru:993/imap/ssl}',
        ],
        self::YANDEX_RU => [
            'id' => self::YANDEX_RU,
            'name' => 'Yandex.ru',
            'server' => 'yandex.ru',
            'imapServer' => '{imap.yandex.ru:993/imap/ssl}',
        ],
    ];

    /**
     * @return array|string[]
     */
    public static function getList(int $id = null): array
    {
        if ($id) {
            return self::LIST[$id] ?? [];
        }

        return self::LIST;
    }

    /**
     * @return array
     */
    public static function getIds()
    {
        return array_keys(self::LIST);
    }

    /**
     * Для заполнения dropDowns, checkBoxes, radioButtons
     * @return array
     */
    public static function getItems(): array
    {
        return ArrayHelper::map(self::LIST, 'id', 'name');
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getName(int $id): string
    {
        return self::LIST[$id]['name'] ?? '#N/A';
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getServer(int $id): string
    {
        return self::LIST[$id]['server'] ?? '#N/A';
    }

    /**
     * @param int $id
     * @param string $folder
     * @return string
     */
    public static function getImapServer(int $id, string $folder = ''): string
    {
        return isset(self::LIST[$id]['imapServer'])
            ? self::LIST[$id]['imapServer'] . $folder
            : '#N/A';
    }
}

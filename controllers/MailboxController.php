<?php

namespace app\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Class MailboxController
 * @package app\controllers
 */
class MailboxController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Данные для вывода хранятся в Yii::$app->mailer->mailbox[header] &
     * Yii::$app->mailer->mailbox[data], см.app\components\Mailer
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => Yii::$app->mailer->getMailboxData(),
            'sort' => [
                'attributes' => ['from', 'subject', 'date'],
                'defaultOrder' => ['date' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'header' => Yii::$app->mailer->getMailboxHeader(),
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Выбираем тело сообщения, загружаем прикрепленные файлы
     * и выводим на просмотр
     *
     * @param int $id
     * @return string
     */
    public function actionMessage(int $id)
    {
        return $this->render('message', [
            'messageBody' => Yii::$app->mailer->getMessageBody($id),
            'messageData' => Yii::$app->mailer->getMailboxData($id),
        ]);
    }

    /**
     * Если задан $id, то формируем ответ (reply) на на имеющееся письмо.
     * Иначе создаем новое пустое сообщение с пустым заголовками To & Subject
     *
     * @param int|null $id
     * @return string
     */
    public function actionCompose(int $id = null)
    {


        return $this->render('compose');
    }
}

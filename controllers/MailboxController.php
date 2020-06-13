<?php

namespace app\controllers;

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


    public function actionIndex()
    {


        return $this->render('index');
    }

    public function actionMessage(int $id)
    {


        return $this->render('message');
    }

    public function actionCompose()
    {


        return $this->render('compose');
    }
}
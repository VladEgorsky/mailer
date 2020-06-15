<?php
/**
 * @var $this yii\web\View
 * @var $header object
 * @var $dataProvider yii\data\ArrayDataProvider
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = Yii::$app->mailer->getCredentials()->email;
$this->params['breadcrumbs'][] = $this->title;

$gridLayout = <<< HTML
    {items}
    <div class="mailbox-controls text-center" style="padding-bottom: 15px">
        {summary} {pager}
    </div>
HTML;

$pagerSettings = [
    'prevPageLabel' => '<i class="fa fa-chevron-left"></i>',
    'nextPageLabel' => '<i class="fa fa-chevron-right"></i>',
    'maxButtonCount' => 5,
    'options' => ['class' => 'pagination', 'style' => 'margin: 0'],
];
?>


<div class="box box-primary">
    <div class="box-header with-border" style="padding: 15px 0">
        <h3 class="box-title">Входящие</h3>

        <div class="pull-right">
        <?= Html::a('Создать сообщение &nbsp; <i class="fa fa-envelope-open-o" aria-hidden="true"></i>',
            Url::to(['mailbox/compose']), ['class' => 'btn btn-sm btn-default']) ?>
        <?= Html::a('Перейти в другой ящик &nbsp; <i class="fa fa-sign-out" aria-hidden="true"></i>',
            Url::to(['site/logout']), ['class' => 'btn btn-sm btn-default']) ?>
        </div>
    </div>

    <div class="box-body no-padding">
            <?php Pjax::begin(['id' => 'grid_pjax', 'class' => 'table-responsive mailbox-messages']); ?>
            <?= GridView::widget([
                'id' => 'grid',
                'dataProvider' => $dataProvider,
                'layout' => $gridLayout,
                'summary' => "<span style='vertical-align: 10px; margin-right: 10px'>Записи {begin}-{end} из {totalCount}</span>",
                'pager' => $pagerSettings,
                'columns' => [
                    [
                        'attribute' => 'flagged',
                        'label' => '',
                        'format' => 'raw',
                        'value' => function($model, $key) {
                            $class = $model['flagged'] ? 'fa fa-star text-yellow' : 'fa fa-star-o text-yellow';
                            return Html::tag('i', ' ',
                                ['class' => $class, 'style' => 'cursor: pointer', 'title' => 'Просмотр']);
                        },
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'hasAttachments',
                        'label' => '',
                        'format' => 'raw',
                        'value' => function($model) {
                            return $model['hasAttachments'] ?
                                Html::tag('i', ' ', ['class' => 'fa fa-paperclip']) : '';
                        },
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'from',
                        'label' => 'От кого',
                        'format' => 'raw',
                        'value' => function($model, $key) {
                            return Html::a($model['from'], Url::to(['mailbox/message', 'id' => $model['uid']]),
                                ['data-pjax' => 0, 'target' => '_blank']);
                        },
                    ],
                    [
                        'attribute' => 'subject',
                        'label' => 'Тема',
                    ],
                    [
                        'attribute' => 'date',
                        'label' => 'Дата',
                        'value' => function($model) {
                            return Yii::$app->formatter->asDatetime($model['date'], 'short');
                        },
                    ],
                    [
                        'format' => 'raw',
                        'value' => function($model, $key) {
                            $text1 = '<i class="fa fa-eye" aria-hidden="true"></i>';
                            $url1 = Url::to(['mailbox/message', 'id' => $model['uid']]);
                            $text2 = '<i class="fa fa-reply" aria-hidden="true"></i>';
                            $url2 = Url::to(['mailbox/compose', 'id' => $model['uid']]);

                            $options1 = ['class' => 'btn btn-sm btn-default', 'data-pjax' => 0,
                                'title' => 'Просмотр', 'target' => '_blank'];
                            $options2 = ['class' => 'btn btn-sm btn-default', 'data-pjax' => 0,
                                'title' => 'Ответить', 'target' => '_blank'];
                            return Html::a($text1, $url1, $options1) . ' ' . Html::a($text2, $url2, $options2);
                        },
                    ],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
            <!-- /.mail-box-messages -->
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->

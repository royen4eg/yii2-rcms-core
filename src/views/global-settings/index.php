<?php

/* @var $this View */

/* @var $model CS */

use rcms\core\helpers\Timezone;
use rcms\core\models\CoreSettings as CS;
use rcms\core\Module as CoreModule;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;

$availModules = ArrayHelper::getColumn(CoreModule::$availableModules, 'name');
?>
<div id="rcms-core-settings-index">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?php $form = ActiveForm::begin(['action' => Url::to(['create'])]) ?>

    <?= $form->field($model, 'projectName') ?>

    <?= $form->field($model, 'tablePrefix') ?>

    <?= $form->field($model, 'activeModules')->dropDownList($availModules, [
        'multiple' => true,
        'prompt' => Yii::t('rcms-core', 'None')
    ]) ?>

    <?= $form->field($model, 'dateFormat') ?>

    <?= $form->field($model, 'datetimeFormat') ?>

    <?= $form->field($model, 'timeFormat') ?>

    <?= $form->field($model, 'timeZone')->dropDownList(Timezone::getAll()) ?>

    <?php if (Yii::$app->authManager instanceof \yii\rbac\ManagerInterface): ?>

        <?= $form->field($model, 'defaultAccessRole')->widget('yii\jui\AutoComplete', [
            'options' => [
                'class' => 'form-control',
            ],
            'clientOptions' => [
                'source' => array_keys(Yii::$app->authManager->getPermissions()),
            ],
        ]);
        ?>

    <?php else: ?>

        <p><?=Yii::t('rcms-core', 'Cannot recognize MangerInterface. Ability to assign role restrictions is unable.')?></p>

    <?php endif; ?>

    <?= $form->field($model, 'useRcmsUser')->radioList([0 => 'No', 1 => 'Yes']) ?>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('rcms-core', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>
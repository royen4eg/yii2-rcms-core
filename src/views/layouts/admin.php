<?php

use rcms\core\AdminModule;
use rcms\core\assets\AdminCoreAssets;
use rcms\core\base\BaseAdminController;
use rcms\core\models\Dictionary;
use rcms\core\widgets\Alerts;
use rcms\core\widgets\flagicon\FlagIcon;
use rcms\core\widgets\pace\PaceAssets;
use rcms\core\widgets\sidenav\SideNav;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Dropdown;
use yii\bootstrap4\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */
/* @var $context BaseAdminController */
AdminCoreAssets::register($this);
PaceAssets::register($this);
?>

<?php $this->beginPage() ?>

<?php
$langs = Dictionary::getAvailLanguages();
unset($langs[Yii::$app->language]);
foreach ($langs as &$lang) {
    $lang = [
        'label' => FlagIcon::widget(['country' => $lang]),
        'url' => ['set-lang', 'lang' => $lang],
        'linkOptions' => ['class' => 'bg-dark']
    ];
}
$langDropdown = Html::a(FlagIcon::widget(['country' => Yii::$app->language]), '#', [
        'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'
    ]) . Dropdown::widget([
        'items' => $langs, 'encodeLabels' => false, 'options' => ['class' => 'bg-dark'],
    ]);
$sidenav = SideNav::widget([
    'items' => BaseAdminController::getSidenavItems($this->context),
    'brandLabel' => Yii::$app->modules[AdminModule::$moduleId]->projectName ?? 'rcms',
    'user' => Yii::$app->user->identity,
    'footer' => [
        [
            'label' => Html::tag('i', '', ['class' => 'fa fa-power-off']),
            'url' => ['logout'],
            'options' => ['title' => Yii::t('rcms-core', 'Logout')]
        ],
        $langDropdown,
    ],
    'footerOptions' => ['class' => 'dropup']
]);
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="wrap theme chiller-theme toggled">
    <?php echo $sidenav ?>
    <main class="page-content">
        <?php if (isset($this->params['breadcrumbs'])) {
            echo Breadcrumbs::widget(['links' => $this->params['breadcrumbs'],]);
        } ?>
        <div class="container-fluid">
            <?= Alerts::widget() ?>
            <?= $content ?>
        </div>
    </main>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

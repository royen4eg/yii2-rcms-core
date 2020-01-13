<?php

use rcms\core\models\Dictionary;
use rcms\core\widgets\flagicon\FlagIcon;
use yii\bootstrap4\Tabs;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap4\ActiveForm;

/* @var $this View */
/* @var $activeCategory string */
/* @var $model Dictionary */

$allLangs = $model::getAvailLanguages();

$tabsDicts = $allDicts = $allModels = [];

foreach ($allLangs as $lang) {
    $tabsDicts[] = [
        'label' => implode(' ', [
            FlagIcon::widget(['country' => $lang]), $lang
        ]),
        'encode' => false,
        'link' => '#',
        'linkOptions' => ['class' => 'rcms-dictionary-link', 'data-target' => $lang],
        'active' => $lang === $model->lang
    ];
    $allDicts[$lang] = (new Dictionary($model->category, $lang))->dictionary;
}

unset($allLangs[$model->lang]);

$tabsDicts[] = [
    'label' => Html::tag('i', '', ['class' => 'fas fa-plus'])
        . ' ' . Yii::t('rcms-core', 'Add Language'),
    'encode' => false,
    'linkOptions' => ['id' => 'rcms-add-dictionary']
];

foreach ($model->dictionary as $original => $translation) {
    $allModels[] = [
        'original' => $original,
        $model->lang => $translation
    ];
}

?>
    <div class="dictionary-default-index">
        <h1><?= $this->title ?></h1>
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => Url::to(['index'])]) ?>
        <div class="input-group">
            <?= Html::dropDownList('category', $activeCategory, $model::getAvailDictionaries(),
                ['id' => 'categoryList', 'class' => 'form-control']
            ); ?>
            <span class="input-group-btn">
            <button type="submit" id="actionList-refresh" class="btn btn-primary">
                <?= Yii::t('rcms-core', 'Load') ?>
                <i class="glyphicon glyphicon-refresh" aria-hidden="true"></i>
            </button>
        </span>
        </div>
        <?php ActiveForm::end() ?>
        <?php $form = ActiveForm::begin() ?>

        <?= $form->field($model, 'category')->label(false)->hiddenInput() ?>

        <?= $form->field($model, 'lang')->label(false)->hiddenInput() ?>

        <?= Tabs::widget([
            'items' => $tabsDicts,
            'id' => 'rcms-dictionary-tabs'
        ]) ?>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $allModels,
                'pagination' => false,
            ]),
            'filterModel' => true,
            'filterOnFocusOut' => false,
            'filterRowOptions' => ['class' => 'table-secondary'],
            'tableOptions' => ['class' => 'table table-hover table-sm'],
            'headerRowOptions' => ['class' => 'thead-light'],
            'caption' => Yii::t('rcms-core', 'Dictionary contain only system messages'),
            'summary' => false,
            'rowOptions' => ['class' => 'rcms-dictionary-row'],
            'columns' => [
                [
                    'attribute' => 'original',
                    'header' => Yii::t('rcms-core', 'Original'),
                    'filter' => Html::textInput(null, null, [
                        'id' => 'rcms-dictionary-filter-original',
                        'class' => 'form-control rcms-dictionary-filter',
                        'placeholder' => Yii::t('rcms-core', 'Filter')
                    ]),
                    'contentOptions' => ['class' => 'rcms-dictionary-filter-original'],
                    'options' => [
                        'width' => '50%',
                    ],
                ],
                [
                    'attribute' => $model->lang,
                    'header' => Yii::t('rcms-core', 'Translation'),
                    'format' => 'raw',
                    'value' => function ($m) use ($form, $model) {
                        $original = $m['original'];
                        return $form->field($model, "dictionary[$original]")->label(false)
                            ->textInput([
                                'class' => 'form-control rcms-dictionary-filter-input rcms-dictionary-filter-' . $model->lang,
                                'data-phrase' => $original
                            ]);
                    },
                    'filter' => Html::textInput(null, null, [
                        'id' => 'rcms-dictionary-filter-' . $model->lang,
                        'class' => 'form-control rcms-dictionary-filter',
                        'placeholder' => Yii::t('rcms-core', 'Filter')
                    ]),
                ]
            ]
        ]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('rcms-core', 'Update'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end() ?>
    </div>
    <div class="hidden">
        <?= Html::a('', '#', [
            'id' => 'rcms-delete-dictionary',
            'data' => [
                'confirm' => Yii::t('rcms-core', "Are you sure you want to delete this dictionary?"),
                'method' => 'post',
                'path' => Url::to(['delete-lang', 'lang' => '']),
            ],
        ]) ?>
    </div>
<?php
$allDictsSrt = json_encode($allDicts, JSON_UNESCAPED_UNICODE);

$insertField = Html::tag('li',
    Html::textInput(null, null, ['class' => 'form-control', 'id' => 'rcms-new-dict-field', 'style' => 'width:50px;']),
    ['class' => 'nav-item active']
);
$newLink = Html::a('', '#', ['class' => 'rcms-dictionary-link nav-link', 'data-toggle' => 'tab']);

$js = <<<JS
let allDicts = {$allDictsSrt};
$('input.rcms-dictionary-filter').keyup(function() {
    $('.rcms-dictionary-row').show();
    $('input.rcms-dictionary-filter').each(function(k,i) {
        const fV = i.value;
        if (fV !== ''){
            $('.' + $(i).attr('id')).each(function(kk,c) {
                console.log(c, c.tagName, c.value);
                const str = c.tagName.toLowerCase() === 'input' ? c.value : c.textContent;
                if (!str.toUpperCase().includes(fV.toUpperCase())){
                    $(c).closest('tr').hide();
                }
            });
        }
    });
});
$(document).on('click', '.rcms-dictionary-link', function() {
    let field = $('#dictionary-lang');
    const activeLang = field.val();
    if (typeof allDicts[activeLang] === "undefined"){
        allDicts[activeLang] = {}
    }
    const newLang = $(this).data('target');
    if (typeof allDicts[newLang] === "undefined"){
        allDicts[newLang] = {}
    }
    $('input.rcms-dictionary-filter-input').each(function(k,i) {
        const phrase = $(i).data('phrase');
        allDicts[activeLang][phrase] = i.value;
        const phraseTrans = (typeof allDicts[newLang][phrase] === "undefined") ? '' : allDicts[newLang][phrase];
        $(i).val(phraseTrans);
    });
    field.val(newLang);
});

$(document).on('dblclick', '.rcms-dictionary-link', function() {
    let tgt = $('#rcms-delete-dictionary'); 
    tgt.attr('href', tgt.data('path') + $('#dictionary-lang').val());
    tgt.click()
});

$('#rcms-add-dictionary').click(function() {
  $('{$insertField}').insertBefore('#rcms-dictionary-tabs > .nav-item:last-child');
  $('#rcms-new-dict-field').focus();
});
$(document).on('blur', '#rcms-new-dict-field', function() {
    if (this.value === ''){
        $(this).parent().remove();
    } else {
      let tgt = $(this).parent().html('{$newLink}').find('a');
      tgt.html(this.value);
      tgt.data('target', this.value);       
    }
});
JS;
$this->registerJs($js);

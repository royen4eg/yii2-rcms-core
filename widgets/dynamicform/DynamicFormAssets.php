<?php
namespace rcms\core\widgets\dynamicform;

use rcms\core\base\BaseAssetBundle;
use yii\widgets\ActiveFormAsset;

class DynamicFormAssets extends BaseAssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'dynamic-form.js'
    ];

    public function init()
    {
        parent::init();
        $this->depends[] = ActiveFormAsset::class;
    }

}
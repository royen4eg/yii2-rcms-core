<?php
namespace rcms\core\widgets\dynamicform;

use rcms\core\base\BaseAssetBundle;
use yii\widgets\ActiveFormAsset;

/**
 * Class DynamicFormAssets
 * @package rcms\core\widgets\dynamicform
 * Original from https://github.com/wbraganca/yii2-dynamicform
 *
 * @author Andrii Borodin
 * @since 0.1
 */
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
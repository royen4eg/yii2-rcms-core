<?php

namespace rcms\core\base;

use rcms\core\assets\AdminCoreAssets;
use yii\web\AssetBundle;

class BaseAssetBundle extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $depends = [
        AdminCoreAssets::class,
    ];
}
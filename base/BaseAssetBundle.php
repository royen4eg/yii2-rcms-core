<?php

namespace rcms\core\base;

use rcms\core\assets\AdminCoreAssets;
use yii\web\AssetBundle;

/**
 * Class BaseAssetBundle
 * @package rcms\core\base
 * @author Andrii Borodin
 * @since 0.1
 */
class BaseAssetBundle extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $depends = [
        AdminCoreAssets::class,
    ];
}
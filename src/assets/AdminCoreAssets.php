<?php
namespace rcms\core\assets;

use yii\bootstrap4\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Core RCMS asset bundle
 *
 * @author Andrii Borodin
 * @since 1.0
 */
class AdminCoreAssets extends AssetBundle
{

    public $sourcePath = '@rcms/core/assets/admin-core';

    public $css = [
        'https://use.fontawesome.com/releases/v5.11.1/css/all.css',
        'css/admin-core.css',
    ];
    public $js = [
        'js/rcms-core.js'
    ];

    public $depends = [
        YiiAsset::class,
        BootstrapPluginAsset::class
    ];

    /**
     * @param \yii\web\View $view
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        $view->registerJs('');
    }
}

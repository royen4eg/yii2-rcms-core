<?php

namespace rcms\core\widgets\sidenav;

use yii\web\AssetBundle;

/**
 * Class SideNavAsset
 * @package rcms\core\widgets\sidenav
 * @author Andrii Borodin
 * @since 0.1
 */
class SideNavAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $css = [
        'css/sidebar.css'
    ];

}
<?php
namespace rcms\core\widgets\pace;

use yii\web\AssetBundle;
use yii\web\View;

class PaceAssets extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $css = [
        'css/pace-plain.css'
    ];

    public $js = [
        'js/pace.min.js'
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}
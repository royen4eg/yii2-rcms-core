<?php
namespace rcms\core\widgets\flagicon;

use yii\web\AssetBundle;

/**
 * Class FlagIconAsset
 * @package rcms\core\widgets\flagicon
 * @author Andrii Borodin
 * @since 0.1
 */
class FlagIconAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $css = [
        'css/rcms-icons.css'
    ];

}
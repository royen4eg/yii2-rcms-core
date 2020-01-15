<?php

namespace rcms\core\widgets\summernote;

use rcms\core\base\BaseAssetBundle;

/**
 * Class SummernoteAsset
 * @package rcms\core\widgets\summernote
 * @author Andrii Borodin
 * @since 0.1
 */
class SummernoteAsset extends BaseAssetBundle
{
    /** @var string */
    public $sourcePath = '@bower/summernote/dist';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $postfix = YII_DEBUG ? '' : '.min';
        $this->css[] = 'summernote-bs4.css';
        $this->js[] = 'summernote-bs4' . $postfix . '.js';
        parent::init();
    }
}
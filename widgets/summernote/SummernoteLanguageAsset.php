<?php

namespace rcms\core\widgets\summernote;

use rcms\core\base\BaseAssetBundle;

class SummernoteLanguageAsset extends BaseAssetBundle
{
    /** @var string */
    public $language;
    /** @var string */
    public $sourcePath = '@bower/summernote/lang';
    /** @var array */
    public $depends = [
        SummernoteAsset::class
    ];
    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        $this->js[] = 'summernote-' . $this->language . '.js';
        parent::registerAssetFiles($view);
    }
}
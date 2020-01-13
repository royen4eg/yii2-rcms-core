<?php

namespace rcms\core\widgets\jsoneditor;

use rcms\core\base\BaseAssetBundle;

class JsonEditorAssets extends BaseAssetBundle
{
    /** @var string */
    public $sourcePath = '@bower/jsoneditor/dist';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $postfix = YII_DEBUG ? '' : '.min';
        $this->css[] = 'jsoneditor' . $postfix . '.css';
        $this->js[] = 'jsoneditor' . $postfix . '.js';
        parent::init();
    }

}

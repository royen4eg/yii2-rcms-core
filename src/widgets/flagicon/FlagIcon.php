<?php

namespace rcms\core\widgets\flagicon;

use yii\bootstrap4\Widget;
use yii\helpers\Html;

class FlagIcon extends Widget
{
    /* @var string */
    public $tag = 'i';

    /* @var string */
    public $country;

    public $options = [];

    private $_replaceMask = [
        'en-us' => 'us'
    ];

    public function beforeRun()
    {
        if (in_array(strtolower($this->country), array_keys($this->_replaceMask))) {
            $this->country = $this->_replaceMask[strtolower($this->country)];
        }

        return parent::beforeRun();
    }

    public function run()
    {
        $asset = FlagIconAsset::register($this->getView());

        $iconPath = $asset->baseUrl . '/icons/' . strtolower($this->country) . '.png';
        Html::addCssClass($this->options, ['rcms-icon flag']);
        Html::addCssStyle($this->options, ['background-image' => "url({$iconPath})"]);
        return Html::tag($this->tag, null, $this->options);
    }

}
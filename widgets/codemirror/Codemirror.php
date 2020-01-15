<?php
namespace rcms\core\widgets\codemirror;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Class Codemirror
 * @package rcms\core\widgets\codemirror
 * @author Andrii Borodin
 * @since 0.1
 */
class Codemirror extends InputWidget
{

    public $assets = [];

    public $events = [];

    public $settings = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textarea($this->name, $this->value, $this->options);
        }
        $this->registerAssets();
    }


    /**
     * Registers Assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        $id = $this->options['id'];
        $jsId = "CodeMirror_" . str_replace('-', '_', $this->options['id']);
        $settings = $this->settings;
        $assets = $this->assets;
        $settings = Json::encode($settings);

        $js = "window.{$jsId} = CodeMirror.fromTextArea(document.getElementById('$id'), $settings);";
        $view->registerJs($js);

        foreach ($this->events as $key => $event) {
            $view->registerJs("window.{$jsId}.on('$key', $event);");
        }
        CodemirrorAsset::register($this->view, $assets);
    }
}
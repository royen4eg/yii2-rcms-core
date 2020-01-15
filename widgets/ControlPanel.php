<?php

namespace rcms\core\widgets;

use yii\bootstrap4\Widget;
use yii\helpers\Html;

/**
 * Class ControlPanel
 * @package rcms\core\widgets
 * @author Andrii Borodin
 * @since 0.1
 */
class ControlPanel extends Widget
{

    const TYPE_DEFAULT = 'button';
    const TYPE_LIST = 'list';
    const TYPE_RESET = 'resetButton';
    const TYPE_SUBMIT = 'submitButton';

    const POSITION_LEFT = 'left';
    const POSITION_RIGHT = 'right';
    const POSITION_DEFAULT = self::POSITION_LEFT;

    public $title;

    /**
     * @var array of buttons that should be rendered
     * Each button is an array of configuration
     * Example of buttons:
     * ```php
     * [
     *      [
     *          'label' => 'Button1',
     *          'options' => [ 'id' => 'button1' ],
     *      ],
     *      [
     *          'label' => 'Button2',
     *          'options' => [ 'id' => 'button2' ],
     *          'url' => 'www.example.com'
     *      ],
     * ]
     * ```
     *
     * If the "url" is set - it will generate <a> tag instead of <button>
     */
    public $leftItems = [];
    public $rightItems = [];

    public $options = [];

    public $containerOptions = [
        'class' => 'navbar-collapse'
    ];

    private $_leftContent;
    private $_rightContent;

    public function beforeRun()
    {
        $this->options['id'] = $this->options['id'] ?? 'rcms-cp-' . $this->id;
        if (isset($this->options['class'])) {
            Html::addCssClass($this->options['class'], 'rcms-cp');
        } else {
            $this->options['class'] = 'navbar navbar-expand-md navbar-light bg-light mb-3 rcms-cp';
        }
        return parent::beforeRun();
    }

    public function run()
    {
        echo Html::beginTag('nav', ['class' => $this->options['class'], 'id' => $this->options['id']]);
        echo Html::beginTag('div', ['class' => $this->containerOptions['class']]);
        echo $this->renderHeader();
        echo $this->renderLeft();
        echo $this->renderRight();
        echo Html::endTag('div');
        echo Html::endTag('nav');

    }

    public function renderHeader()
    {
        $content = Html::beginTag('div', ['class' => 'navbar-header']);
        $content .= Html::tag('span', $this->title, ['class' => 'navbar-brand']);
        $content .= Html::endTag('div');
        return $content;
    }

    public function renderLeft()
    {
        if (empty($this->leftItems)) {
            return '';
        }
        $content = Html::beginTag('div', ['class' => 'navbar-nav mr-auto']);
        $content .= $this->renderButtons($this->leftItems);
        $content .= Html::endTag('div');
        return $content;
    }

    public function renderRight()
    {
        if (empty($this->rightItems)) {
            return '';
        }
        $content = $this->renderButtons($this->rightItems);
        return $content;
    }

    public function renderButtons($buttons)
    {
        $content = '';
        foreach ($buttons as $button) {
            if (is_string($button)) {
                $content .= $button;
                continue;
            }

            $button['type'] = $button['type'] ?? self::TYPE_DEFAULT;
            if (isset($button['url']) && !empty($button['url'])) {
                $content .= Html::a($button['label'], $button['url'], $button['options']);
            } elseif (isset($button['items'])) {
                $content .= Html::tag('div', $this->renderButtons($button['items']), [
                    'class' => 'btn-group'
                ]);
            } else {
                $type = $button['type'];
                $content .= Html::$type($button['label'], $button['options']);
            }

        }


        return $content;
    }

}
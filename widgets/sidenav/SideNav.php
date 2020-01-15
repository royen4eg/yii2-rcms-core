<?php

namespace rcms\core\widgets\sidenav;

use yii\base\Widget;
use yii\helpers\Html;
use Yii;
use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * Class SideNav
 * @package rcms\core\widgets\sidenav
 * @author Andrii Borodin
 * @since 0.1
 */
class SideNav extends Widget
{
    const POS_LEFT = 'left';

    const POS_RIGHT = 'right';

    const LINK_TEMPLATE = '{link}';

    /**
     * @var array of items.
     *
     * Base structure of item:
     *
     * ```php
     *  'itemId' => [
     *      'label' => string,
     *      'icon' => string|false,
     *      'access' => array|false,
     *      'url' => string,
     *      'template' => string
     *  ],
     * ```
     */
    public $items;

    public $footer = [];

    public $brandLabel = false;

    public $populateBreadcrumbs = true;

    public $brandUrl = false;

    public $brandOptions = [];

    public $headerOptions = [];

    public $menuOptions = [];

    public $footerOptions = [];

    public $request;

    private $_requestedMenuRoute = [];

    public $options = [
        'class' => 'rcms-sidebar-wrapper'
    ];

    /* @var IdentityInterface */
    public $user;

    public $position = self::POS_LEFT;

    public $active = true;

    public function init()
    {
        parent::init();
        SideNavAsset::register($this->getView());

        echo Html::a('<i class="fas fa-bars"></i>', '#', [
            'class' => 'btn btn-sm btn-dark show-sidebar',
            'id' => 'rcms-show-sidebar-' . $this->id
        ]);

        $this->options['id'] = $this->options['id'] ?? 'rcms-sidebar-' . $this->id;
        $position = 'rcms-sidebar-' . $this->position;
        Html::addCssClass($this->options, [$position]);
        if ($this->active) {
            Html::addCssClass($this->options, ['active']);
        }

        if ($this->request) {
            $this->_requestedMenuRoute = explode('/', $this->request);
        }
        echo Html::beginTag('nav', $this->options);
    }

    public function run()
    {
        $view = $this->view;
        $content = Html::beginTag('div', ['class' => 'rcms-sidebar-content']);
        $content .= $this->renderBrand();
        $content .= $this->renderHeader();
        $content .= $this->renderMenu();
        $content .= Html::endTag('div');
        $content .= $this->renderFooter();
        $content .= Html::endTag('nav');

        $closeId = 'rcms-close-sidebar-' . $this->id;
        $showId = 'rcms-show-sidebar-' . $this->id;

        $view->registerJs("$('#{$closeId}').click(function() { $(this).closest('.rcms-sidebar-wrapper').parent().removeClass('toggled');});$('#{$showId}').click(function() { $(this).parent().addClass('toggled');});", $view::POS_READY);
        return $content;
    }

    public function renderBrand()
    {
        Html::addCssClass($this->brandOptions, ['widget' => 'sidebar-brand']);
        $content = Html::beginTag('div', $this->brandOptions);
        if ($this->brandLabel) {
            $content .= Html::a($this->brandLabel, $this->brandUrl ?? Yii::$app->homeUrl);
        }
        $content .= Html::tag('div', '<i class="fas fa-times"></i>', [
            'id' => 'rcms-close-sidebar-' . $this->id,
            'class' => 'close-sidebar'
        ]);
        $content .= Html::endTag('div');
        return $content;
    }

    public function renderHeader()
    {
        Html::addCssClass($this->headerOptions, ['widget' => 'sidebar-header']);
        $content = Html::beginTag('div', $this->headerOptions);
        if (!empty($this->user)) {
            $content .= $this->renderUser();
        }
        $content .= Html::endTag('div');
        return $content;
    }

    public function renderUser()
    {
        $content = '';
        $pic = Html::img('https://raw.githubusercontent.com/azouaoui-med/pro-sidebar-template/gh-pages/src/img/user.jpg', [
            'alt' => 'Avatar',
            'class' => 'img-responsive img-rounded'
        ]);
        $content .= Html::tag('div', $pic, ['class' => 'rcms-user-pic']);
        $content .= Html::tag('div',
            Html::tag('span', $this->user->username, ['class' => 'rcms-user-name']),
            ['class' => 'user-info']);
        return $content;
    }

    public function renderMenu()
    {
        Html::addCssClass($this->menuOptions, ['widget' => 'sidebar-menu']);
        $content = Html::beginTag('div', $this->menuOptions);
        $content .= $this->renderList();
        $content .= Html::endTag('div');
        return $content;
    }

    public function renderFooter()
    {
        if (empty($this->footer)) {
            return '';
        } elseif (is_string($this->footer)) {
            return $this->footer;
        }
        Html::addCssClass($this->footerOptions, ['widget' => 'sidebar-footer']);
        $content = Html::beginTag('div', $this->footerOptions);
        foreach ($this->footer as $footerItem) {
            if (is_string($footerItem)) {
                $content .= $footerItem;
            } elseif (isset($footerItem['label'])) {
                $url = $footerItem['url'] ?? '#';
                $options = $footerItem['options'] ?? [];
                $content .= $this->renderLink($footerItem['label'], $url, $options);
            }
        }
        $content .= Html::endTag('div');
        return $content;
    }

    public function renderList()
    {
        return Html::tag('ul', $this->renderItems($this->items, $this->request), [
            'class' => 'rcms-sidebar-menu'
        ]);
    }

    /**
     * @param $items
     * @param int $level
     * @return string
     */
    public function renderItems($items, $level = 0)
    {
        $list = [];
        $i = 0;
        foreach ($items as $key => $params) {
            $permissions = $params['access'] ?? [];
            if ($this->checkPermissions($permissions)) {
                $options = $params['options'] ?? [];
                $params['active'] = $params['active'] ?? false;
                if ($this->populateBreadcrumbs && $params['active'] && isset($params['label'])) {
                    $this->view->params['breadcrumbs'][] = $params['label'];
                }
                if (isset($params['items'])) {
                    $params['name'] = $params['name'] ?? $key;
                    $content = $this->renderTreeItem($params, $level);
                    Html::addCssClass($options, "sidebar-dropdown");
                } else {
                    $icon = isset($params['icon']) ? $this->renderIcon($params['icon']) : '';
                    if (isset($params['isHeader']) && $params['isHeader']) {
                        $content = Html::tag('span', $icon . $params['label']);
                        Html::addCssClass($options, "header-menu");
                    } elseif (isset($params['url'])) {
                        $linkOptions = $params['linkOptions'] ?? [];
                        $content = $this->renderLink($icon . $params['label'], Url::to($params['url']),
                            array_merge([
                                'class' => $params['active'] ? 'active' : '', 'id' => 'rcms-link-' . $key
                            ], $linkOptions));
                        unset($linkOptions);
                    } else {
                        $content = '';
                    }
                }
                $template = $params['template'] ?? static::LINK_TEMPLATE;
                $content = str_replace(static::LINK_TEMPLATE, $content, $template);
                $position = $params['position'] ?? $i;
                $list[$position] = Html::tag('li', $content, $options);
            }
            $i++;
        }
        ksort($list);
        return implode('', $list);
    }

    private function renderTreeItem($params, $level)
    {
        $icon = isset($params['icon']) ? $this->renderIcon($params['icon']) : '';
        $label = $icon . Html::tag('span', $params['label']);
        $id = implode('', [$params['name'], 'Collapse']);

        $content = Html::a($label, '#' . $id, [
            'class' => $params['active'] ? '' : 'collapsed',
            'role' => 'button',
            'aria-expanded' => $params['active'] ? 'true' : 'false',
            'aria-controls' => $id,
            'data-toggle' => 'collapse'
        ]);

        $content .= Html::beginTag('div', [
            'id' => $id,
            'class' => implode(' ', [
                'sidebar-submenu collapse',
                $params['active'] ? 'show' : ''
            ])
        ]);
        $content .= Html::tag('ul', $this->renderItems($params['items'], $level + 1));
        $content .= Html::endTag('div');

        return $content;
    }

    protected function renderLink($label, $url, $options)
    {
        return Html::a($label, $url, $options);
    }

    /**
     * @param $icon string
     * @return string
     */
    protected function renderIcon($icon)
    {
        if (empty($icon)) {
            return '';
        }
        return Html::tag('i', '', [
            'class' => 'rcms-menuicon ' . $icon
        ]);
    }


    /**
     * @param array $permissions
     * @return bool
     */
    private function checkPermissions(array $permissions)
    {
        $allow = false;
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if (Yii::$app->user->can($permission)) {
                    $allow = true;
                    break;
                }
            }
        } else {
            $allow = true;
        }
        return $allow;
    }
}
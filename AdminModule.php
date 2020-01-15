<?php

namespace rcms\core;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\helpers\Url;

/**
 * Class AdminModule
 * @package rcms\core
 * @author Andrii Borodin
 * @since 0.1
 */
class AdminModule extends Module implements BootstrapInterface
{
    const RCMS_PARAM_NAME = 'rcmsAdminModuleId';

    /** @var string */
    public static $moduleId;

    /**
     * @var array of menu elements that will appear in sidenav
     */
    public static $sidenavItems = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        self::$sidenavItems['mainMenuHeader'] = [
            'label' => Yii::t('rcms-core', 'Main menu'),
            'isHeader' => true
        ];

    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     * @throws \yii\base\InvalidConfigException
     */
    public function bootstrap($app)
    {
        $app->params[self::RCMS_PARAM_NAME] = static::$moduleId = $this->id;

        parent::bootstrap($app);

        self::$sidenavItems['global-settings'] = [
            'label' => Yii::t('rcms-core', 'Core'),
            'icon' => 'fas fa-cogs',
            'items' => [
                'index' => [
                    'label' => Yii::t('rcms-core', 'Global Settings'),
                    'access' => [],
                    'url' => Url::to("/{$this->id}/global-settings")
                ]
            ]
        ];

        self::$sidenavItems['dictionary'] = [
            'label' => Yii::t('rcms-core', 'Dictionary'),
            'icon' => 'fas fa-language',
            'url' => Url::to("/{$this->id}/dictionary")
        ];

    }

    public function beforeAction($action)
    {
        Yii::$app->view->params['breadcrumbs'][] = [
            'label' => 'RCMS',
            'url' => Url::to("/{$this->id}")
        ];
        return parent::beforeAction($action);
    }

}
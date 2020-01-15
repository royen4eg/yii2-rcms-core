<?php
namespace rcms\core;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * Class FrontModule
 * @package rcms\core
 * @author Andrii Borodin
 * @since 0.1
 */
class FrontModule extends Module implements BootstrapInterface
{
    const RCMS_PARAM_NAME = 'rcmsFrontModuleId';

    /** @var string */
    public static $moduleId;

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\web\Application) {

        }
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
    }
}
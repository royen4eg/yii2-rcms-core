<?php

namespace rcms\core;

use rcms\core\base\BootstrapConsoleInterface;
use rcms\core\components\JsonMessageSource;
use rcms\core\components\LanguageSelector;
use rcms\core\components\SessionAlerts;
use rcms\core\components\UserSettings;
use rcms\core\models\CoreSettings;
use rcms\core\models\User;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\web\IdentityInterface;

/**
 * Class Module
 * @package modules\rcms\core
 *
 * @property SessionAlerts $alerts
 * @property LanguageSelector $languageSelector
 * @property CoreSettings $settings
 * @property UserSettings $userSettings
 * @property string $projectName
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    const BASE_PATH = __DIR__;

    const BASE_ALIAS = '@rcms/core';

    const RCMS_PARAM_NAME = 'rcmsCoreModuleId';

    const BOOTSTRAP_DEF = 4;

    const BOOTSTRAP_V3 = 3;

    const BOOTSTRAP_V4 = 4;

    const DB_COMPONENT = 'rcmsDb';

    /** @var array */
    public $migrationPath = [self::BASE_ALIAS . '/migrations'];

    /** @var array of modules installed for rcms */
    public static $availableModules = [];

    /**
     * @var IdentityInterface the model that describes the basic user object
     */
    public $userModel;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // Apply core path as alias
        Yii::setAlias(self::BASE_ALIAS, self::BASE_PATH);

        // Initiate translation module if not set yet
        if (!isset(Yii::$app->i18n->translations['rcms*'])) {
            Yii::$app->i18n->translations['rcms*'] = [
                'class' => JsonMessageSource::class,
                'sourceLanguage' => 'en',
            ];
        }
        parent::init();
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        // Assign global parameter to application
        $app->params[self::RCMS_PARAM_NAME] = $this->id;
        $app->params['bsVersion'] = self::BOOTSTRAP_DEF;

        $this->scanModules();

        // Activate all basic components
        $this->setComponents($this->coreComponents());
        $this->setDatabaseConnection($app);
        $this->setUserModule($app);

        if ($app instanceof \yii\web\Application) {
            // Initiate user model
            $this->userModel = $app->user;

            // Run component bootstrap methods (if exists)
            foreach ($this->components as $id => $class) {
                $component = $this->get($id);
                if ($component instanceof BootstrapInterface) {
                    $component->bootstrap($app);
                }
            }

            $this->activateModules($app);

            $app->on(Application::EVENT_AFTER_ACTION, [$this, 'populateDictionary']);

        } elseif ($app instanceof \yii\console\Application) {
            foreach ($this->components as $id => $class) {
                $component = $this->get($id);
                if ($component instanceof BootstrapConsoleInterface) {
                    $component->bootstrapConsole($app);
                }
            }

            $this->activateConsoleModules($app);
        }

        $this->setDateFormat($app);
    }

    protected function coreComponents()
    {
        return [
            'languageSelector' => LanguageSelector::class,
            'settings' => CoreSettings::class,
            'alerts' => SessionAlerts::class,
            'userSettings' => UserSettings::class
        ];
    }

    /**
     * @return string|null
     */
    public function getProjectName()
    {
        return $this->settings->projectName;
    }


    protected function populateDictionary()
    {
        $translations = Yii::$app->getI18n()->translations;
        foreach ($translations as $key => $t) {
            $populate = false;
            if (is_array($t) && isset($t['class']) && $t['class'] === JsonMessageSource::class) {
                $t = Yii::$app->getI18n()->getMessageSource($key);
                $populate = true;
            } elseif ($t instanceof JsonMessageSource) {
                $populate = true;
            }

            if ($populate) {
                $t->populateWithMessages();
            }
        }
    }

    protected function activateModules(Application $app)
    {
        $activeModules = $this->settings->activeModules;
        if (empty($activeModules) || !is_array($activeModules)){
            return;
        }
        foreach (self::$availableModules as $moduleId => $module) {
            if (in_array($moduleId, $this->settings->activeModules)) {
                $this->setModule($moduleId, ['class' => $module['class']]);
                $module = $this->getModule($moduleId);
                if ($module instanceof BootstrapInterface) {
                    $module->bootstrap($app);
                }
            }
        }
    }

    protected function activateConsoleModules(\yii\console\Application $app)
    {
        $activeModules = $this->settings->activeModules;
        if (empty($activeModules) || !is_array($activeModules)){
            return;
        }
        foreach (self::$availableModules as $moduleId => $module) {
            if (in_array($moduleId, $this->settings->activeModules)) {
                $this->setModule($moduleId, ['class' => $module['class']]);
                $module = $this->getModule($moduleId);
                if ($module instanceof BootstrapConsoleInterface) {
                    $module->bootstrapConsole($app);
                }
            }
        }
    }

    protected function setDatabaseConnection(Application $app)
    {
        $rcmsDatabase = clone $app->db;
        $rcmsDatabase->tablePrefix = $this->settings->tablePrefix;
        $app->setComponents([self::DB_COMPONENT => $rcmsDatabase]);
    }

    protected function setUserModule(Application $app)
    {
        if ($this->settings->useRcmsUser) {
            $app->get('user')->identityClass = User::class;
        }
    }

    protected function setDateFormat(Application $app)
    {
        foreach (['dateFormat', 'datetimeFormat', 'timeFormat'] as $format) {
            if (isset($this->settings->$format) && !empty($this->settings->$format)) {
                $app->formatter->$format = 'php:' . $this->settings->$format;
            }
        }
        $app->timeZone = $app->formatter->timeZone = $this->settings->timeZone;
    }

    private function scanModules()
    {
        $currentClass = self::class;
        $pos = strrpos($currentClass, 'core');
        if ($pos !== false) {
            $projectDir = Yii::getAlias(self::BASE_ALIAS);
            $directories = array_values(array_diff(scandir($projectDir), ['.', '..']));
            foreach ($directories as $dir) {
                $tgtClass = substr_replace($currentClass, $dir, $pos, 4);
                if (class_exists($tgtClass) && defined("$tgtClass::MODULE_NAME")) {
                    self::$availableModules[$dir] = [
                        'class' => $tgtClass,
                        'name' => $tgtClass::MODULE_NAME
                    ];
                }
            }
        }

    }

}
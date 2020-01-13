<?php

namespace rcms\core\components;

use rcms\core\base\BaseAdminController;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Component;
use Yii;

class LanguageSelector extends Component implements BootstrapInterface
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * set application language and save it to session
     * @param string $value
     */
    public function setLanguage($value)
    {
        Yii::$app->session['rcmsLang'] = $value;
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if (isset($app->session['rcmsLang'])) {
            //Restore language, saved in session
//            $app->sourceLanguage =
            $app->language =
//            $app->formatter->locale =
                    $app->session['rcmsLang'];

        } else {
            // detect language by browser headers
            $language = $app->request->getPreferredLanguage();
            $this->setLanguage($language);
        }
    }
}
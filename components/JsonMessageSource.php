<?php


namespace rcms\core\components;

use rcms\core\models\Dictionary;
use Yii;
use yii\i18n\PhpMessageSource;

/**
 * Class JsonMessageSource
 * @package rcms\core\components
 * @author Andrii Borodin
 * @since 0.1
 */
class JsonMessageSource extends PhpMessageSource
{
    public $basePath = '@rcms/core/messages';

    protected $emptyMessage = [];

    public function translate ($category, $message, $language)
    {
        $trans = parent::translate($category, $message, $language);
        if(!isset($this->emptyMessage[$category])){
            $this->emptyMessage[$category] = [];
        }
        if(!in_array($message, $this->emptyMessage[$category])){
            $this->emptyMessage[$category][] = $message;
        }
        return $trans;
    }

    public function populateWithMessages ()
    {
        foreach ($this->emptyMessage as $category => $messages){
            Dictionary::populateIfNotExist($category, Yii::$app->language , $messages);
        }
        return;
    }
}
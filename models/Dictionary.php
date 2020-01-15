<?php

namespace rcms\core\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\i18n\MessageSource;

/**
 * Class Dictionary
 * @package rcms\core\models
 * @author Andrii Borodin
 * @since 0.1
 */
class Dictionary extends Model
{
    const LANG_FILE_MASK = "return [:data];";
    const LANG_DATA_ROW = '":key" => ":value",';

    public $category;
    public $lang;
    public $dictionary;

    /**
     * @var MessageSource
     */
    private $_translationModule;

    public function __construct($category, $lang = null, array $config = [])
    {
        $this->category = $category;
        $this->lang = $lang ?? Yii::$app->language;

        $this->_translationModule = Yii::$app->i18n->translations['rcms*'];
        if (is_array($this->_translationModule)) {
            $this->_translationModule = Yii::createObject($this->_translationModule);
        }
        parent::__construct($config);
        $this->loadContent();
    }

    public function rules()
    {
        return [
            [['category', 'lang'], 'required'],
            [['category', 'lang'], 'string'],
            [['dictionary'], 'each', 'rule' => ['string']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'category' => Yii::t('rcms-core', 'Category'),
            'lang' => Yii::t('rcms-core', 'Language'),
            'dictionary' => Yii::t('rcms-core', 'Dictionary'),
        ];
    }

    public function attributeHints()
    {
        return [
            'category' => Yii::t('rcms-core', '<code>Double click the tab to remove language</code>'),
        ];
    }

    private function loadRawContent()
    {
        $path = Url::to($this->_translationModule->basePath) . '/' . $this->lang . '/' . $this->category . '.json';
        $this->dictionary = file_exists($path) ? file_get_contents($path) : '';
    }

    private function loadContent()
    {
        $path = Url::to($this->_translationModule->basePath) . '/' . $this->lang . '/' . $this->category . '.php';
        $this->dictionary = file_exists($path) ? (require $path) : [];
    }

    public function save()
    {
        if ($this->validate()) {
            $path = Url::to($this->_translationModule->basePath) . '/' . $this->lang . '/' . $this->category;
            file_put_contents($path . '.json', json_encode($this->dictionary, JSON_UNESCAPED_UNICODE));
            return true;
        }
        return false;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $path = Url::to($this->_translationModule->basePath) . '/' . $this->lang . '/' . $this->category;
        if (!file_exists($path . '.php')) {
            $this->generateControlFile($path . '.php', $this->category);
        }
        ksort($this->dictionary);
        return parent::validate($attributeNames, $clearErrors);
    }

    protected function generateControlFile($path, $c)
    {
        FileHelper::createDirectory(dirname($path));
        $content = "<?php if(!file_exists(__DIR__.'/{$c}.json')){touch(__DIR__.'/{$c}.json');} return json_decode(file_get_contents(__DIR__.'/{$c}.json', FILE_USE_INCLUDE_PATH), true);";
        file_put_contents($path, $content);
    }

    protected function removeLanguage() {
        $path = Url::to($this->_translationModule->basePath) . '/' . $this->lang;
        FileHelper::removeDirectory($path);
    }

    public static function populateIfNotExist($category, $lang, array $messages)
    {
        $self = new self($category, $lang);
        $dictionary = is_string($self->dictionary) ? json_decode($self->dictionary, true) : $self->dictionary;
        $oldEnK = (is_array($dictionary)) ? array_keys($dictionary) : [];
        foreach ($messages as $m) {
            if (!in_array($m, $oldEnK)) {
                $dictionary[$m] = '';
            }
        }
        $self->dictionary = $dictionary;
        $self->save();
    }

    public static function getTranslationModule()
    {
        $t = \Yii::$app->i18n->translations['rcms*'];
        if (is_array($t)) {
            $t = Yii::createObject($t);
        }
        return $t;
    }

    public static function getAvailDictionaries()
    {
        $t = self::getTranslationModule();
        $pattern = Url::to($t->basePath) . '/*/*.php';
        $availTypes = [];
        foreach (glob($pattern) as $filePath) {
            $pathinfo = pathinfo($filePath);
            $availTypes[$pathinfo['filename']] = $pathinfo['filename'];
        }
        return $availTypes;
    }

    public static function getAvailLanguages()
    {
        $t = self::getTranslationModule();
        $pattern = Url::to($t->basePath) . '/*';
        $availLangs = [];
        foreach (glob($pattern) as $filePath) {
            $pathinfo = pathinfo($filePath);
            $availLangs[$pathinfo['filename']] = $pathinfo['filename'];
        }
        if (empty($availLangs)) {
            $availLangs = [Yii::$app->language];
        }
        return $availLangs;
    }

    public function deleteLanguage()
    {
        if(!empty($this->lang)){
            $this->removeLanguage();
        }
    }
}
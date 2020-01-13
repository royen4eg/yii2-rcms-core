<?php


namespace rcms\core\components;


use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/***
 * Class UserSettings
 * @package rcms\core\components
 *
 * @property array $data
 */
class UserSettings extends Component
{
    const REPLACEMENT_PARAM = '{id}';

    const CONFIG_PATH = '@rcms/core/config';

    const FILENAME_MASK = 'userSettings_{id}.data';

    private $_data = [];

    private $_userId;

    /** @var string */
    private $_fileDir;

    public function __construct($config = [])
    {
        if(empty($filePath)){
            $this->_fileDir = Yii::getAlias(self::CONFIG_PATH);
        } else {
            $this->_fileDir = $filePath;
        }
        parent::__construct($config);
    }

    public function getData()
    {
        if (empty($this->_data)){
            $this->_userId = Yii::$app->user->id;
            $this->_data = $this->getUserSettings();
        }
        return $this->_data;
    }

    /**
     * @param $module
     * @param $param
     * @return mixed
     */
    public function get($module, $param)
    {
        $path = implode('.', [$module, $param]);
        return ArrayHelper::getValue($this->data, $path);
    }

    /**
     * @param $module string
     * @param $param string
     * @param $value mixed
     */
    public function set($module, $param, $value)
    {
        $path = implode('.', [$module, $param]);
        ArrayHelper::setValue($this->_data, $path, $value);
        $this->save();
    }

    public function getUserSettings()
    {
        return $this->loadSettings($this->_userId);
    }

    public function getSettingsById($id)
    {
        $user = Yii::$app->user->identity::findIdentity($id);
        if(!empty($user)) {
            return $this->loadSettings($id);
        }
        return [];
    }

    protected function loadSettings($id)
    {
        if(empty($id)) {
            return null;
        }

        $userFileName = str_replace(static::REPLACEMENT_PARAM, $id, self::FILENAME_MASK);
        $path = $this->_fileDir . '/' . $userFileName;

        $content = '';
        $fp = @fopen($path, 'r');
        if ($fp !== false) {
            @flock($fp, LOCK_SH);
            $content = fread($fp, filesize($path));
            @flock($fp, LOCK_UN);
            fclose($fp);
        }

        if ($content !== '') {
            $data = array_reverse(\Opis\Closure\unserialize($content), true);
            return $data;
        }

        return [];
    }

    private function save()
    {
        $userFileName = str_replace(static::REPLACEMENT_PARAM, $this->_userId, self::FILENAME_MASK);
        $path = $this->_fileDir . '/' . $userFileName;
        FileHelper::createDirectory(dirname($path));

        $serializedDAta = \Opis\Closure\serialize($this->_data);
        file_put_contents($path, $serializedDAta);
    }
}
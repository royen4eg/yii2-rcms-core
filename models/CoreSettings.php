<?php

namespace rcms\core\models;

use rcms\core\AdminModule;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

/**
 * Class CoreSettings
 * @package rcms\core\models
 * @author Andrii Borodin
 * @since 0.1
 */
class CoreSettings extends Model
{
    const CONFIG_PATH = '@rcms/core/config';

    const FILENAME_DEFAULT = 'globalSettings.data';

    const DATE_FORMAT_DEFAULT = 'd-M-Y';

    const TIME_FORMAT_DEFAULT = 'H:i:s';

    const DATETIME_FORMAT_DEFAULT = 'd-M-Y ' . self::TIME_FORMAT_DEFAULT;

    const TIMEZONE_DEFAULT = 'UTC';

    /** @var string */
    private $_filePath;

    /** @var string */
    public $projectName;
    /** @var string */
    public $tablePrefix;
    /** @var string */
    public $dateFormat;
    /** @var string */
    public $datetimeFormat;
    /** @var string */
    public $timeFormat;
    /** @var string */
    public $timeZone;
    /** @var array */
    public $activeModules = [];
    /** @var string */
    public $defaultAccessRole;
    /** @var string */
    public $coreParams;
    /** @var boolean */
    public $useRcmsUser;

    public function __construct($filePath = null, $config = [])
    {
        if(empty($filePath)){
            $this->_filePath = Yii::getAlias(self::CONFIG_PATH) . '/' . self::FILENAME_DEFAULT;
        } else {
            $this->_filePath = $filePath;
        }
        parent::__construct($config);

        $this->find();

        if (empty($this->dateFormat)){
            $this->dateFormat = self::DATE_FORMAT_DEFAULT;
        }
        if (empty($this->datetimeFormat)){
            $this->datetimeFormat = self::DATETIME_FORMAT_DEFAULT;
        }
        if (empty($this->timeFormat)){
            $this->timeFormat = self::TIME_FORMAT_DEFAULT;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['projectName', 'dateFormat', 'datetimeFormat', 'timeFormat', 'useRcmsUser'], 'required'],
            [['projectName', 'tablePrefix', 'dateFormat', 'datetimeFormat', 'timeFormat', 'timeZone', 'defaultAccessRole'], 'string'],
            [['useRcmsUser'], 'boolean'],
            [['dateFormat'], 'default', 'value' => self::DATE_FORMAT_DEFAULT],
            [['datetimeFormat'], 'default', 'value' => self::DATETIME_FORMAT_DEFAULT],
            [['timeFormat'], 'default', 'value' => self::TIME_FORMAT_DEFAULT],
            [['timeZone'], 'default', 'value' => self::TIMEZONE_DEFAULT],
            [['defaultAccessRole'], 'checkAccessRole'],
            [['useRcmsUser'], 'checkUserTable'],
            ['activeModules', 'each', 'rule' => ['string']],
            [$this->attributes(), 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'projectName' => Yii::t('rcms-core','Project Name'),
            'tablePrefix' => Yii::t('rcms-core','Table Prefix'),
            'dateFormat' => Yii::t('rcms-core','Date Format'),
            'datetimeFormat' => Yii::t('rcms-core','Datetime Format'),
            'timeFormat' => Yii::t('rcms-core','Time Format'),
            'timeZone' => Yii::t('rcms-core','Timezone'),
            'defaultAccessRole' => Yii::t('rcms-core','Default Access Role'),
            'activeModules' => Yii::t('rcms-core','Active Modules'),
            'coreParams' => Yii::t('rcms-core','Core Parameters'),
            'useRcmsUser' => Yii::t('rcms-core','Use RCMS User Module'),
        ];
    }

    public function attributeHints()
    {
        return [
            'projectName' => Yii::t('rcms-core','This variable will be used as Admin Logo'),
            'tablePrefix' => Yii::t('rcms-core','This variable will be used as table prefix in database. Note: you need to make sure all tables are available for RCMS to run correctly!'),
            'activeModules' => Yii::t('rcms-core','Selected modules will allow use additional functionality of RCMS'),
            'dateFormat' => Yii::t('rcms-core','Current format') . ': ' . Yii::$app->formatter->asDate(time()),
            'timeFormat' => Yii::t('rcms-core','Current format') . ': ' . Yii::$app->formatter->asTime(time()),
            'timeZone' => Yii::t('rcms-core','Current timezone') . ': ' . Yii::$app->formatter->timeZone,
            'datetimeFormat' => Yii::t('rcms-core','Current format') . ': ' . Yii::$app->formatter->asDatetime(time())
                . ' ' . Yii::t('rcms-core','Format details on') . ': <code>https://www.php.net/manual/en/function.date.php</code>',
            'defaultAccessRole' => Yii::t('rcms-core','This role will be used to restrict access to RCMS admin UI. '
                . '<code>Note:</code> if you don\'t have access to this role, you won\'t be able to set it.'),
            'useRcmsUser' => Yii::t('rcms-core','There is a risk of logout after changing')
        ];
    }

    public function afterValidate()
    {
        parent::afterValidate();
    }


    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function save()
    {
        if($this->validate()){
            $path = $this->_filePath;
            FileHelper::createDirectory(dirname($path));
            $data = $this->getAttributes();

            $serializedDAta = \Opis\Closure\serialize($data);
            file_put_contents($path, $serializedDAta);

            Yii::$app->getModule(AdminModule::$moduleId)->bootstrap(Yii::$app);
            return true;

        }
        return false;
    }

    public function find()
    {
        $path = $this->_filePath;

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
            $this->load($data, '');
        }
        return $this;
    }

    public function checkAccessRole($attribute)
    {
        if(!Yii::$app->user->can($this->$attribute)) {
            $this->addError($attribute, Yii::t('rcms-core','User should have access to selected role'));
        }
    }

    public function checkUserTable($attribute)
    {
        if($this->$attribute) {
            $tableSchema = User::getDb()->schema->getTableSchema(User::tableName());
            if(empty($tableSchema)){
                $this->addError($attribute, Yii::t('rcms-core','There is no RCMS User table in Database. Please run migration before applying RCMS User as identifier'));
            }
        }
    }
}
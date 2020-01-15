<?php

namespace rcms\core\base;

use rcms\core\Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/**
 * Class ActiveRecord
 * @package rcms\core\base
 *
 *
 * @author Andrii Borodin
 * @since 0.1
 */
class ActiveRecord extends \yii\db\ActiveRecord
{

    /**
     * Returns the database connection used by this AR class.
     * It will use default connection "db" application component to modify it to RCMS use.
     * You may override this method if you want to use a different database connection.
     * @return Connection the database connection used by this AR class.
     * @throws InvalidConfigException
     */
    public static function getDb()
    {
        $connection = Yii::$app->get(Module::DB_COMPONENT);
        if ($connection instanceof Connection){
            return $connection;
        }
        throw new InvalidConfigException();
    }
}
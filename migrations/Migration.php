<?php
namespace rcms\core\migrations;

use Yii;

/**
 * Class Migration
 * @package rcms\core\migrations
 * @author Andrii Borodin
 * @since 0.1
 */
class Migration extends \yii\db\Migration
{

    public function init()
    {
        if(isset(Yii::$app->rcmsDb)){
            $this->db = Yii::$app->rcmsDb;
        }
        parent::init();
    }
}
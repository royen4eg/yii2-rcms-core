<?php
namespace rcms\core\migrations;

use Yii;

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
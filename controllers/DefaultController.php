<?php


namespace rcms\core\controllers;

use rcms\core\AdminModule;
use rcms\core\base\BaseAdminController;

/**
 * Class DefaultController
 * @package rcms\core\controllers
 * @author Andrii Borodin
 * @since 0.1
 *
 * @property AdminModule $module
 */
class DefaultController extends BaseAdminController
{

    public $availableActions = [
        parent::ACTION_INDEX
    ];

    public function actionIndex()
    {
        return $this->render('index');
    }

}
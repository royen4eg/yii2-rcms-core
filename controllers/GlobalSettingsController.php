<?php

namespace rcms\core\controllers;

use rcms\core\AdminModule;
use rcms\core\base\BaseAdminController;
use rcms\core\models\CoreSettings;
use Yii;

/**
 * Class GlobalSettingsController
 * @package rcms\core\controllers
 * @property AdminModule $module
 */
class GlobalSettingsController extends BaseAdminController
{

    public $availableActions = [
        parent::ACTION_INDEX,
        parent::ACTION_CREATE
    ];

    /**
     *{@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->view->title = Yii::t('rcms-core', 'Core Settings');

        $this->modelObject = new CoreSettings();
        if (parent::beforeAction($action)) {
            return true;
        }
        return false;
    }

    public function actionIndex()
    {
        return $this->render('index', ['model' => $this->modelObject]);
    }

    public function actionCreate($exit = true)
    {
        if (Yii::$app->request->isPost) {
            $this->modelObject->load(Yii::$app->request->post());
            if ($this->modelObject->save()) {
                Yii::$app->getSession()->addFlash('success', 'Content successfully saved');
                Yii::$app->getSession()->addFlash('info', 'Some settings may require reload the page to be applied');
            } else
                Yii::$app->getSession()->addFlash('error', json_encode($this->modelObject->errors, JSON_UNESCAPED_UNICODE));
        }
        return $this->render('index', ['model' => $this->modelObject]);
    }
}

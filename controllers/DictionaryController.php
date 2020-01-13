<?php


namespace rcms\core\controllers;


use rcms\core\base\BaseAdminController;
use rcms\core\models\Dictionary;
use Yii;

class DictionaryController extends BaseAdminController
{
    public $availableActions = [
        parent::ACTION_INDEX,
        parent::ACTION_CREATE,
        'delete-lang'
    ];

    /**
     *{@inheritdoc}
     */
    public function beforeAction($action)
    {
        $this->view->title = Yii::t('rcms-core', 'Dictionary');

        if (parent::beforeAction($action)) {
            return true;
        }
        return false;
    }

    public function actionIndex($category = 'rcms-core', $lang = null)
    {
        $this->modelObject = new Dictionary($category, $lang);
        if (\Yii::$app->request->isPost) {
            $this->modelObject->load(\Yii::$app->request->post());
            if($this->modelObject->save()){
                \Yii::$app->session->addFlash('success', Yii::t('rcms-core', 'Content successfully saved'));
            } else {
                $errorMsgs = implode('<br>', $this->modelObject->getErrorSummary(true));
                \Yii::$app->session->addFlash('warning', Yii::t('rcms-core', 'Could not save the content:') . $errorMsgs);
            }
        }

        return $this->render('index', [
            'activeCategory' => $category,
            'model' => $this->modelObject,
        ]);
    }

    public function actionDeleteLang($lang)
    {
        if (\Yii::$app->request->isPost) {
            $this->modelObject = new Dictionary('rcms-core', $lang);
            $this->modelObject->deleteLanguage();
        }
        return $this->redirect('index');
    }
}
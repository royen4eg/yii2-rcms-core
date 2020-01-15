<?php

namespace rcms\core\base;

use rcms\core\AdminModule;
use Yii;
use yii\base\Model;
use yii\base\ViewContextInterface;
use yii\bootstrap4\Html;
use yii\db\ActiveRecordInterface;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Application;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class BaseAdminController
 * @package modules\rcms\core\base
 * @author Andrii Borodin
 * @since 0.1
 *
 * @property Model|SearchInterface|ActiveRecordInterface $modelObject
 * @property SearchInterface $modelSearch
 */
class BaseAdminController extends Controller
{
    const ACTION_INDEX = 'index';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';

    const ACTION_LOGOUT = 'logout';
    const ACTION_LANG = 'set-lang';

    public static $baseActions = [
        self::ACTION_LOGOUT, self::ACTION_LANG
    ];

    /* @var Model|SearchInterface|ActiveRecordInterface */
    public $modelObject;

    /* @var SearchInterface */
    public $modelSearch;

    /**
     * @var string
     */
    public $primaryKey;

    /** @var array  */
    public $availableActions = [];

    const AUTH_ROLE_DEFAULT = '@';

    private $_accessPermission = self::AUTH_ROLE_DEFAULT;

    public function init()
    {
        parent::init();
        $dar = Yii::$app->modules[AdminModule::$moduleId]->settings->defaultAccessRole;
        if(!empty($dar)){
            $this->_accessPermission = $dar;
        }
    }

    /** @inheritDoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['logout'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'roles' => [$this->_accessPermission],
                    ],
                ],
            ],
            'verbs' => [
                'class' => 'yii\filters\VerbFilter',
                'actions' => [
                    self::ACTION_INDEX => ['get'],
                    self::ACTION_VIEW => ['get'],
                    self::ACTION_CREATE => ['get', 'post'],
                    self::ACTION_UPDATE => ['get', 'post'],
                    self::ACTION_DELETE => ['post'],
                    self::ACTION_LANG => ['get']
                ],
            ],
        ];
    }



    /**
     *{@inheritdoc}
     * @throws \yii\web\BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function beforeAction($action)
    {
        $module = $this->module;
        while (true) {
            if ($module instanceof AdminModule) {
                break;
            } elseif ($module instanceof Application || !$module->module) {
                throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'), 404);
            }
            $module = $module->module;
        }

        $this->layout = 'admin';

        if (!empty($this->modelObject) && empty($this->modelSearch)) {
            $this->modelSearch = $this->modelObject;
        }

        if (in_array($action->id, array_merge($this->availableActions, self::$baseActions))) {
            return parent::beforeAction($action);
        }
        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'), 404);
    }

    /**
     * Primary page of model object. Usually presents list of items from that model
     * @return string Page Content
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'model' => $this->modelObject,
            'searchModel' => $this->modelSearch,
            'dataProvider' => $this->modelSearch->search($_GET)
        ]);
    }

    /**
     * Basic create action
     * @param bool $exit
     * @return string Page Content
     */
    public function actionCreate($exit = true)
    {
        if (Yii::$app->request->isPost) {
            $this->modelObject->load(Yii::$app->request->post());
            if ($this->modelObject->save()) {
                Yii::$app->getSession()->addFlash('success', 'Content successfully saved');
                if ($exit) {
                    return $this->redirect('index');
                } else {
                    return $this->redirect(['update', 'id' => $this->modelObject->{$this->primaryKey}]);
                }
            } else
                Yii::$app->getSession()->addFlash('error', json_encode($this->modelObject->errors, JSON_UNESCAPED_UNICODE));
        }
        return $this->render('object', ['model' => $this->modelObject]);
    }

    /**
     * Basic view action
     * @param $id
     * @return string|Response
     */
    public function actionView($id)
    {
        $object = $this->modelObject->findOne([$this->primaryKey => $id]);
        if (empty($object)) {
            Yii::$app->getSession()->addFlash('warning', 'Item not found');
            return $this->redirect('index');
        }
        return $this->render('view', ['model' => $object]);
    }

    /**
     * Basic update action
     * @param $id
     * @param bool $exit
     * @return string|Response
     */
    public function actionUpdate($id, $exit = false)
    {
        $object = $this->modelObject->findOne([$this->primaryKey => $id]);
        if (empty($object)) {
            Yii::$app->getSession()->addFlash('warning', 'Item not found');
            return $this->redirect('index');
        }
        if (Yii::$app->request->isPost) {
            $object->load(Yii::$app->request->post());
            if ($object->save()) {
                Yii::$app->getSession()->addFlash('success', 'Content successfully updated');
                if ($exit) {
                    return $this->redirect('index');
                }
            } else {
                Yii::$app->getSession()->addFlash('error', json_encode($object->errors, JSON_UNESCAPED_UNICODE));
            }
        }
        return $this->render('object', ['model' => $object]);
    }

    /**
     * Basic delete action
     * @param $id
     * @return string Page Content
     */
    public function actionDelete($id)
    {
        if (Yii::$app->request->isPost) {
            $object = $this->modelObject->findOne([$this->primaryKey => $id]);
            if (empty($object)) {
                Yii::$app->getSession()->addFlash('warning', 'Item not found');
                return $this->redirect('index');
            } else {
                if ($object->delete()) {
                    Yii::$app->getSession()->addFlash('success', 'Content successfully deleted');
                } else {
                    Yii::$app->getSession()->addFlash('error', json_encode($object->errors, JSON_UNESCAPED_UNICODE));
                }
            }
        } else {
            Yii::$app->getSession()->addFlash('error', 'Invalid request Method');
        }
        return $this->redirect('index');
    }

    /**
     * Logout action.
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Action to get session language through entire project interface
     * @param string $lang of selected language
     * @return Response
     */
    public function actionSetLang(string $lang)
    {
        Yii::$app->session['rcmsLang'] = $lang;
        return $this->redirect(Yii::$app->request->referrer);
    }

    public static function getSidenavItems(ViewContextInterface $controller)
    {
        $path = [$controller->id => ['active' => true, 'items' => [$controller->action->id => ['active' => true]]]];
        $module = $controller->module;
        while (true) {
            if ($module instanceof AdminModule) {
                $res = array_replace_recursive(AdminModule::$sidenavItems, $path);
                $func = function ($item) use (&$func, &$callback) {
                    if (isset($item['items'])) {
                        $haveValidItems = implode('', ArrayHelper::getColumn($item['items'], 'label', false));
                        if (empty($haveValidItems)) {
                            unset($item['items']);
                        } else {
                            $items = array_map($func, $item);
                        }
                    }
                    return isset($item) && is_array($item) ? array_map($func, $item) : $item;
                };
                $res = array_map($func, $res);
                return $res;
            } elseif ($module instanceof Application || !$module->module) {
                return [];
            }
            $path = [$module->id => ['active' => true, 'items' => $path]];
            $module = $module->module;
        }
        return [];
    }

}
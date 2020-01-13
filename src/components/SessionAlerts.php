<?php
namespace rcms\core\components;

use Yii;
use yii\base\Component;
use yii\bootstrap4\Alert;

class SessionAlerts extends Component
{
    const CATEGORIES = [
        'error',
        'warning',
        'success',
        'info',
    ];

    const CATEGORY_CLASSES = [
        'error' => 'danger',
        'warning' => 'warning',
        'success' => 'success',
        'info' => 'info',
    ];

    public function addFlash($category, $message)
    {
        if(in_array($category, self::CATEGORIES)){
            Yii::$app->session->addFlash($category, $message);
        }
    }

    public function getFlashes()
    {
        return Yii::$app->session->getFlashes();
    }

    public function renderFlashes()
    {
        $flashMessages = Yii::$app->session->getFlashes();
        if ($flashMessages) {
            $content = [];
            foreach($flashMessages as $key => $message) {
                $content[] = Alert::widget([
                    'options' => [
                        'class' => 'alert-' . self::CATEGORY_CLASSES[$key],
                    ],
                    'body' => $message,
                ]);
            }
            return implode('', $content);
        }

        return null;
    }
}
<?php

namespace rcms\core\behaviors;

use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\helpers\Json;

class JsonBehavior extends Behavior
{
    const ACTION_FIND = 'onAfterFind';
    const ACTION_SAVE = 'onBeforeSave';

    public $property;
    public $jsonField;

    /**
     * @return array
     */
    public function events() : array
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => static::ACTION_FIND,
            ActiveRecord::EVENT_BEFORE_INSERT => static::ACTION_SAVE,
            ActiveRecord::EVENT_BEFORE_UPDATE => static::ACTION_SAVE,
            ActiveRecord::EVENT_AFTER_INSERT => static::ACTION_FIND,
            ActiveRecord::EVENT_AFTER_UPDATE => static::ACTION_FIND,
        ];
    }

    /**
     * @param Event $event
     */
    public function onAfterFind(Event $event): void
    {
        /* @var ActiveRecord $model */
        $model = $event->sender;
        $jsonField = $this->getJsonField($model);
        $attribute = $model->getAttribute($jsonField);
        if(!is_array($attribute)){
            $model->{$this->property} = Json::decode($attribute);
        }
    }

    /**
     * @param Event $event
     */
    public function onBeforeSave(Event $event): void
    {
        /* @var ActiveRecord $model */
        $model = $event->sender;
        $jsonField = $this->getJsonField($model);
        if(is_array($model->{$this->property}) || is_object($model->{$this->property})){
            $model->setAttribute($jsonField, Json::encode($model->{$this->property}));
        }
    }

    /**
     * @param ActiveRecord $model
     * @return string
     */
    protected function getJsonField(ActiveRecord $model): string
    {
        $jsonField = $this->jsonField ?? $this->property;
        if (!$model->hasAttribute($jsonField)){
            $msg = "Field {field} with type JSON does not exist in the table {table}";
            throw new \DomainException(\Yii::t('rcms-core', $msg, [ 'field' => $jsonField, 'table' => $model::tableName()]));
        }
        return $jsonField;
    }
}
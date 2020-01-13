<?php


namespace rcms\core\base;


use yii\base\Model;
use yii\data\DataProviderInterface;

abstract class BaseSearchModel extends Model implements SearchInterface
{

    /**
     * @var string item id
     */
    public $id;

    /**
     * @var int the default page size
     */
    public $pageSize = 25;

    /**
     * @param array $params
     * @return DataProviderInterface
     */
    public abstract function search(array $params = []): DataProviderInterface;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [$this->attributes(), 'safe'],
        ];
    }

}
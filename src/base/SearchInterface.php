<?php
namespace rcms\core\base;

use yii\data\DataProviderInterface;

interface SearchInterface
{
    /**
     * Method used to make basic search in model
     * @param array $params
     * @return DataProviderInterface
     */
    public function search(array $params = []): DataProviderInterface;
}
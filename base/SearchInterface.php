<?php
namespace rcms\core\base;

use yii\data\DataProviderInterface;

/**
 * Interface SearchInterface
 * @package rcms\core\base
 * @author Andrii Borodin
 * @since 0.1
 */
interface SearchInterface
{
    /**
     * Method used to make basic search in model
     * @param array $params
     * @return DataProviderInterface
     */
    public function search(array $params = []): DataProviderInterface;
}
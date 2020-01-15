<?php

namespace rcms\core\models;

/**
 * This is the ActiveQuery class for [[User]].
 * @author Andrii Borodin
 * @since 0.1
 *
 * @see User
 */
class UserQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere('[[status]]=' . User::STATUS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     * @return User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}

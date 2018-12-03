<?php

    namespace application\entities\tables;

    use b2db\Criteria;

    /**
     * @Table(name="users")
     * @Entity(class="\application\entities\User")
     */
    class Users extends \b2db\Table
    {

        public function getByUsername($username)
        {
            $crit = $this->getCriteria();
            $crit->addWhere('users.username', $username);

            return $this->selectOne($crit);
        }

    }

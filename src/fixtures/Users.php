<?php

    namespace application\entities\tables;

    use b2db\Table;

    /**
     * @Table(name="users")
     * @Entity(class="\application\entities\User")
     */
    class Users extends Table
    {

        public function getByUsername($username)
        {
            $query = $this->getQuery();
            $query->where('users.username', $username);

            return $this->selectOne($query);
        }

    }

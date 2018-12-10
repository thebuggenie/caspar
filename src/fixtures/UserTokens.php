<?php

    namespace application\entities\tables;

    use b2db\Table,
        b2db\Criterion;

    /**
     * @Table(name="user_tokens")
     * @Entity(class="\application\entities\UserToken")
     */
    class UserTokens extends Table
    {

    	public function getByToken($token)
	    {
	    	$query = $this->getQuery();
	    	$query->where('user_tokens.token', $token);

	    	return $this->selectOne($query);
	    }

	    public function getValidByUserId($user_id, $timeout = 86400)
	    {
	    	$query = $this->getQuery();
	    	$query->where('user_tokens.user_id', $user_id);
	    	$query->where('user_tokens.created_at', time() - $timeout, Criterion::GREATER_THAN_EQUAL);

	    	return $this->selectOne($query);
	    }

    }
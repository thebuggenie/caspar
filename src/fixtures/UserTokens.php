<?php

    namespace application\entities\tables;
    use b2db\Criteria;

    /**
     * @Table(name="user_tokens")
     * @Entity(class="\application\entities\UserToken")
     */
    class UserTokens extends \b2db\Table
    {

    	public function getByToken($token)
	    {
	    	$crit = $this->getCriteria();
	    	$crit->addWhere('user_tokens.token', $token);

	    	return $this->selectOne($crit);
	    }

	    public function getValidByUserId($user_id, $timeout = 86400)
	    {
	    	$crit = $this->getCriteria();
	    	$crit->addWhere('user_tokens.user_id', $user_id);
	    	$crit->addWhere('user_tokens.created_at', time() - $timeout, Criteria::DB_GREATER_THAN_EQUAL);

	    	return $this->selectOne($crit);
	    }

    }
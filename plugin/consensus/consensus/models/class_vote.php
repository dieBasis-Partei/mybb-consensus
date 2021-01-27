<?php

class Vote {

    private $choice_id;
    private $user_id;

    public function __construct($choice_id, $user_id) {
        $this->choice_id = $choice_id;
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getChoiceId()
    {
        return $this->choice_id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    public function toDBArray() {
        return array('choice_id' => $this->choice_id,
            'user_id' => $this->user_id
        );
    }

}
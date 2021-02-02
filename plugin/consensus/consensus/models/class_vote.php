<?php

class Vote {

    private $vote_id;
    private $proposal_id;
    private $user_id;
    private $points;

    public function __construct($proposal_id, $user_id, $points, $vote_id = 0) {
        $this->proposal_id = $proposal_id;
        $this->user_id = $user_id;
        $this->points = $points;
        $this->vote_id = $vote_id;
    }

    /**
     * @return int|mixed
     */
    public function getVoteId()
    {
        return $this->vote_id;
    }

    /**
     * @return mixed
     */
    public function getProposalId()
    {
        return $this->proposal_id;
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    public function toDBArray() {
        return array('proposal_id' => $this->proposal_id,
            'user_id' => $this->user_id,
            'points' => $this->points
        );
    }

}
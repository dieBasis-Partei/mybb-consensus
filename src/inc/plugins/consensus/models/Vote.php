<?php

namespace mybb\consensus\models;

class Vote implements DBModel {

    private $vote_id;
    private $proposal_id;
    private $user_id;
    private $points;

    public function __construct(int $proposal_id,
                                int $user_id,
                                int $points,
                                int $vote_id = 0) {
        $this->proposal_id = $proposal_id;
        $this->user_id = $user_id;
        $this->points = $points;
        $this->vote_id = $vote_id;
    }

    /**
     * @return int
     */
    public function getVoteId(): int
    {
        return $this->vote_id;
    }

    /**
     * @return int
     */
    public function getProposalId(): int
    {
        return $this->proposal_id;
    }

    /**
     * @return int
     */
    public function getPoints(): int
    {
        return $this->points;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function toDBArray(): array {
        return array(
            'proposal_id' => $this->proposal_id,
            'user_id' => $this->user_id,
            'points' => $this->points
        );
    }

}
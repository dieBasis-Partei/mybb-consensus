<?php

namespace mybb\consensus\models;

use PHPUnit\Framework\TestCase;

class VoteTest extends TestCase
{

    public function testNewVote(): void {
        $proposal_id = 4;
        $user_id = 2;
        $points = 8;

        $vote = new Vote($proposal_id, $user_id, $points);

        $this->assertEquals($proposal_id, $vote->getProposalId());
        $this->assertEquals($user_id, $vote->getUserId());
        $this->assertEquals($points, $vote->getPoints());
        $this->assertEquals(0, $vote->getVoteId());

        $db_array = ["proposal_id" => $proposal_id,
            "user_id" => $user_id,
            "points" => $points];
        $this->assertEquals($db_array, $vote->toDBArray());
    }

    public function testExistingVote(): void {
        $proposal_id = 4;
        $user_id = 2;
        $points = 8;
        $vote_id = 23;

        $vote = new Vote($proposal_id, $user_id, $points, $vote_id);

        $this->assertEquals($proposal_id, $vote->getProposalId());
        $this->assertEquals($user_id, $vote->getUserId());
        $this->assertEquals($points, $vote->getPoints());
        $this->assertEquals($vote_id, $vote->getVoteId());

        $db_array = ["proposal_id" => $proposal_id,
            "user_id" => $user_id,
            "points" => $points];
        $this->assertEquals($db_array, $vote->toDBArray());
    }

}

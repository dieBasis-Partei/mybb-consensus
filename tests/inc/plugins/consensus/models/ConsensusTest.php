<?php

namespace mybb\consensus\models;

use PHPUnit\Framework\TestCase;
use DateTime;

class ConsensusTest extends TestCase
{

    public function testNewConsensus(): void {
        // given
        $title = 'consensus title';
        $description = 'consensus description';
        $expires = new DateTime();
        $user_id = 4711;
        $thread_id = 5322;
        $status_id = 3;
        $proposal = [];

        // when
        $consensus = new Consensus($title, $description, $expires, $user_id, $thread_id, $status_id, $proposal);

        // then
        $this->assertEquals($title, $consensus->getTitle());
        $this->assertEquals($description, $consensus->getDescription());
        $this->assertEquals($expires, $consensus->getExpires());
        $this->assertEquals($user_id, $consensus->getUserId());
        $this->assertEquals($thread_id, $consensus->getThreadId());
        $this->assertEquals($status_id, $consensus->getStatusId());
        $this->assertEquals($proposal, $consensus->getProposals());
        $this->assertEquals(0, $consensus->getConsensusId());

        $db_array = [
            "title" => $title,
            "description" => $description,
            "expires" => $expires->format(Consensus::DATETIME_FORMAT),
            "creator" => $user_id,
            "thread_id" => $thread_id,
            "status" => $status_id
        ];

        $this->assertEquals($db_array, $consensus->toDBArray());
    }

    public function testExistingConsensus(): void {
        // given
        $title = 'consensus title';
        $description = 'consensus description';
        $expires = new DateTime();
        $user_id = 4711;
        $thread_id = 5322;
        $status_id = 3;
        $proposal = [];
        $consensus_id = 5;

        // when
        $consensus = new Consensus($title, $description, $expires, $user_id, $thread_id, $status_id, $proposal, $consensus_id);

        // then
        $this->assertEquals($title, $consensus->getTitle());
        $this->assertEquals($description, $consensus->getDescription());
        $this->assertEquals($expires, $consensus->getExpires());
        $this->assertEquals($user_id, $consensus->getUserId());
        $this->assertEquals($thread_id, $consensus->getThreadId());
        $this->assertEquals($status_id, $consensus->getStatusId());
        $this->assertEquals($proposal, $consensus->getProposals());
        $this->assertEquals($consensus_id, $consensus->getConsensusId());

        $db_array = [
            "title" => $title,
            "description" => $description,
            "expires" => $expires->format(Consensus::DATETIME_FORMAT),
            "creator" => $user_id,
            "thread_id" => $thread_id,
            "status" => $status_id
        ];

        $this->assertEquals($db_array, $consensus->toDBArray());
    }


}

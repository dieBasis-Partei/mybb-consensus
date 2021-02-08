<?php

namespace mybb\consensus\models;

use PHPUnit\Framework\TestCase;

class ProposalTest extends TestCase
{

    public function testNewProposal(): void {
        // given
        $title = "title";
        $description = "description";
        $position = 10;
        $consensus_id = 47;


        $proposal = new Proposal($title, $description, $position, $consensus_id);

        $this->assertEquals($title, $proposal->getTitle());
        $this->assertEquals($description, $proposal->getDescription());
        $this->assertEquals($position, $proposal->getPosition());
        $this->assertEquals($consensus_id, $proposal->getConsensusId());
        $this->assertEquals(0, $proposal->getId());

        $db_array = ["title" => $title,
            "description" => $description,
            "position" => $position,
            "consensus_id" => $consensus_id];
        $this->assertEquals($db_array, $proposal->toDBArray());
    }

    public function testExistingProposal(): void {
        // given
        $title = "title";
        $description = "description";
        $position = 10;
        $consensus_id = 47;
        $proposal_id = 18;


        $proposal = new Proposal($title, $description, $position, $consensus_id, $proposal_id);

        $this->assertEquals($title, $proposal->getTitle());
        $this->assertEquals($description, $proposal->getDescription());
        $this->assertEquals($position, $proposal->getPosition());
        $this->assertEquals($consensus_id, $proposal->getConsensusId());
        $this->assertEquals($proposal_id, $proposal->getId());

        $db_array = ["title" => $title,
            "description" => $description,
            "position" => $position,
            "consensus_id" => $consensus_id];
        $this->assertEquals($db_array, $proposal->toDBArray());
    }

}

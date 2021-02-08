<?php

namespace mybb\consensus\models;

use DateTime;

class Consensus implements DBModel
{
    public const DATETIME_FORMAT = "Y-m-d H:i:s";

    private $consensus_id;
    private $title;
    private $description;
    private $expires;
    private $user_id;
    private $thread_id;
    private $status_id;
    private $proposals;

    /**
     * Consensus constructor.
     *
     * @param string $title
     * @param string $description
     * @param DateTime $expires
     * @param int $user_id
     * @param int $thread_id
     * @param int $status_id
     * @param array $proposals of Proposal objects
     * @param int $consensus_id optional
     */
    public function __construct(string $title,
                                string $description,
                                DateTime $expires,
                                int $user_id,
                                int $thread_id,
                                int $status_id,
                                array $proposals,
                                int $consensus_id = 0)
    {
        $this->title = $title;
        $this->description = $description;
        $this->expires = $expires;
        $this->user_id = $user_id;
        $this->thread_id = $thread_id;
        $this->status_id = $status_id;
        $this->proposals = $proposals;
        $this->consensus_id = $consensus_id;
    }

    /**
     * Adds an proposal to the consensus object.
     *
     * @param Proposal $proposal
     */
    public function addProposal(Proposal $proposal) {
        $this->proposals[] = $proposal;
    }

    /**
     * @return int
     */
    public function getConsensusId(): int
    {
        return $this->consensus_id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return DateTime
     */
    public function getExpires(): DateTime
    {
        return $this->expires;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getThreadId(): int
    {
        return $this->thread_id;
    }

    /**
     * @return int
     */
    public function getStatusId(): int
    {
        return $this->status_id;
    }

    /**
     * @return array
     */
    public function getProposals(): array
    {
        return $this->proposals;
    }

    public function toDBArray(): array {
        return array(
                'title' => $this->getTitle(),
                'description' => $this->description,
                'expires' => $this->expires->format(Consensus::DATETIME_FORMAT),
                'creator' => $this->user_id,
                'thread_id' => $this->thread_id,
                'status' => $this->status_id
        );
    }

}
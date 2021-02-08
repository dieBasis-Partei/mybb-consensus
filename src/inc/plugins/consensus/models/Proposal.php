<?php

namespace mybb\consensus\models;

class Proposal implements DBModel
{
    private $id;
    private $title;
    private $description;
    private $consensus_id;
    private $position;

    public function __construct(string $title, string $description, int $position, int $consensus_id, int $id = 0) {
        $this->id = $id;
        $this->description = $description;
        $this->title = $title;
        $this->position = $position;
        $this->consensus_id = $consensus_id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getConsensusId(): int
    {
        return $this->consensus_id;
    }

    /**
     * @param int $consensus_id
     */
    public function setConsensusId(int $consensus_id)
    {
        $this->consensus_id = $consensus_id;
    }


    public function toDBArray(): array
    {
        return array(
            "title" => $this->getTitle(),
            "description" => $this->getDescription(),
            "position" => $this->getPosition(),
            "consensus_id" => $this->getConsensusId()
        );
    }

}
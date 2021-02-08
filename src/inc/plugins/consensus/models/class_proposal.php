<?php


class Proposal
{
    private $id;
    private $title;
    private $description;
    private $consensus_id;
    private $position;

    public function __construct($title, $description, $position, $consensus_id, $id = 0) {
        $this->id = $id;
        $this->description = $description;
        $this->title = $title;
        $this->position = $position;
        $this->consensus_id = $consensus_id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getConsensusId()
    {
        return $this->consensus_id;
    }

    /**
     * @param int $consensus_id
     */
    public function setConsensusId($consensus_id)
    {
        $this->consensus_id = $consensus_id;
    }


    public function toDBArray() {
        return array(
            "title" => $this->title,
            "description" => $this->description,
            "position" => $this->position,
            "consensus_id" => $this->consensus_id
        );
    }

}
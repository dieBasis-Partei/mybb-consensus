<?php


class Suggestion
{
    private $title;
    private $description;
    private $consensus_id;

    public function __construct($title, $description, $consensus_id) {
        $this->description = $description;
        $this->title = $title;
        $this->consensus_id = $consensus_id | 0;
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
            "consensus_id" => $this->consensus_id
        );
    }

}
<?php


class Consensus
{
    private $title;
    private $description;
    private $expires;
    private $user_id;
    private $thread_id;
    private $status_id;
    private $suggestions;

    public function __construct($title, $description, DateTime $expires, $user_id, $thread_id, $status_id, $suggestions) {
        $this->title = $title;
        $this->description = $description;
        $this->expires = $expires;
        $this->suggestions = $suggestions;
        $this->user_id = $user_id;
        $this->thread_id = $thread_id;
        $this->status_id = $status_id;
    }

    public function add_suggestion(Suggestion $suggestion) {
        $this->suggestions[] = $suggestion;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getThreadId()
    {
        return $this->thread_id;
    }

    /**
     * @return string
     */
    public function getStatusId()
    {
        return $this->status_id;
    }

    /**
     * @return Array
     */
    public function getSuggestions()
    {
        return $this->suggestions;
    }

    public function toDBArray() {
        return array(
                'title' => $this->getTitle(),
                'description' => $this->description,
                'expires' => $this->expires->format("Y-m-d H:i:s"),
                'creator' => $this->user_id,
                'thread_id' => $this->thread_id,
                'status' => $this->status_id
        );
    }

}
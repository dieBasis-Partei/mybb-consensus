<?php


class Status
{
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';

    private $id;
    private $status;

    public function __construct($status, $id = null) {
        $this->status = $status;
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }




}
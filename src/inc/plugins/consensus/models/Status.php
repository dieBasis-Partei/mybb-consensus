<?php
namespace mybb\consensus\models;

class Status
{
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';

    private $id;
    private $status;

    public function __construct(string $status, int $id = 0) {
        $this->status = $status;
        $this->id = $id;
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
    public function getStatus(): string
    {
        return $this->status;
    }

}
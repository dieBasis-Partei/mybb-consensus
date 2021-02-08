<?php

namespace mybb\consensus\models;

use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{

    public function testNewStatus(): void {
        $status = 'disabled';
        $status_obj = new Status($status);

        $this->assertEquals($status, $status_obj->getStatus());
        $this->assertEquals(0, $status_obj->getId());
    }

    public function testNewStatusWithId(): void {
        $status = 'disabled';
        $status_id = 4711;
        $status_obj = new Status($status, $status_id);

        $this->assertEquals($status, $status_obj->getStatus());
        $this->assertEquals($status_id, $status_obj->getId());
    }

    public function testStatusConstants(): void {
        $this->assertEquals("active", Status::STATUS_ACTIVE);
        $this->assertEquals("closed", Status::STATUS_CLOSED);
        $this->assertEquals("expired", Status::STATUS_EXPIRED);
        $this->assertEquals("inactive", Status::STATUS_INACTIVE);
    }

}

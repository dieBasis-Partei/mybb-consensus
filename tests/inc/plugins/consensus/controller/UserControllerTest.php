<?php

namespace mybb\consensus\controller;

use PHPUnit\Framework\TestCase;

final class UserControllerTest extends TestCase
{
    public function testIsModerator(): void {
        $user['ismoderator'] = 1;
        $this->assertTrue(UserController::is_mod($user));
    }

    public function testIsNotModerator(): void {
        $user['ismoderator'] = 0;
        $this->assertFalse(UserController::is_mod($user));
    }

    public function testIsUnknownModerator(): void {
        $user['ismoderator'] = '';
        $this->assertFalse(UserController::is_mod($user));
    }

}

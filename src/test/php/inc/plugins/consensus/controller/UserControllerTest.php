<?php

namespace inc\plugins\consensus\controller;

use UserController;
use PHPUnit\Framework\TestCase;

final class UserControllerTest extends TestCase
{

    public function testIsModerator(): void {
        $user['ismoderator'] = 1;
        $this->assertTrue(UserController::is_mod($user));
    }

}

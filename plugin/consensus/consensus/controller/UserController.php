<?php

class UserController
{
    public static function is_mod($user) {
        if ($user != null && $user['ismoderator'] != null) {
            return $user['ismoderator'] == 1;
        }

        return false;
    }


}
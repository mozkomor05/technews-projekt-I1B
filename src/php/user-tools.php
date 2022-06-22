<?php

use Vokativ\Name;

class UserTools
{
    public static function getNiceName($user = null)
    {
        if ($user == null) {
            $user = LoginTools::getUser();
        }

        return ucfirst($user->first_name) . " " . ucfirst($user->last_name);
    }

    public static function vokativ($user = null)
    {
        if ($user == null) {
            $user = LoginTools::getUser();
        }

        return ucfirst((new Name())->vokativ($user->first_name));
    }

    public static function getAvatar($user = null)
    {
        if ($user == null) {
            $user = LoginTools::getUser();
        }

        $path = __DIR__ . '/../' . $user->avatar;

        if (empty($user->avatar) || ! file_exists($path)) {
            return '/assets/img/graphics/avatar.png';
        }

        return $user->avatar . '?v=' . filemtime($path);
    }

    public static function fetchUser($userName): ?object
    {
        $user = App::getDb()->queryFirstRow('SELECT * FROM users WHERE user_name = %s', $userName);

        if ( ! $user) {
            return null;
        }

        return (object)$user;
    }
}
<?php

class LoginTools
{
    public static function login(array $user)
    {
        $_SESSION['user'] = $user['user_name'];
        App::setUser((object)$user);
    }

    public static function logout()
    {
        unset($_SESSION['user']);
        App::setUser(null);
    }

    public static function getUser(): ?object
    {
        $loadedUser = App::getUser();

        if ($loadedUser) {
            return $loadedUser;
        }

        if (isset($_SESSION['user'])) {
            $user = UserTools::fetchUser($_SESSION['user']);

            if ( ! $user) {
                self::logout();

                return null;
            }

            return $user;
        }

        return null;
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }
}
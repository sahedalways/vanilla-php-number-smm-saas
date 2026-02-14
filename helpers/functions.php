<?php
// 1️⃣ Random string generator
function generateUsername($length = 7)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    $username = '';
    for ($i = 0; $i < $length; $i++) {
        $username .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $username;
}

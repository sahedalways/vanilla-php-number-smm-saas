<?php
class Wallet
{
    public static function add($user_id, $amount, $conn)
    {
        $stmt = $conn->prepare("UPDATE user_data SET balance = balance + ? WHERE id=?");
        $stmt->execute([$amount, $user_id]);
    }

    public static function deduct($user_id, $amount, $conn)
    {
        $stmt = $conn->prepare("UPDATE user_data SET balance = balance - ? WHERE id=?");
        $stmt->execute([$amount, $user_id]);
    }
}

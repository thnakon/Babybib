<?php
/**
 * Babybib API - Get New CAPTCHA
 * =============================
 */

header('Content-Type: application/json; charset=utf-8');
require_once '../../includes/session.php';

$captcha_num1 = rand(1, 9);
$captcha_num2 = rand(1, 9);
$_SESSION['captcha_answer'] = $captcha_num1 + $captcha_num2;

echo json_encode([
    'success' => true,
    'captcha' => "$captcha_num1 + $captcha_num2 = ?"
]);

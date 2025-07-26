<?php
require __DIR__ . '/firebase-init.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

try {
    $signInResult = $auth->signInWithEmailAndPassword($email, $password);
    $firebaseUser = $signInResult->firebaseUserId();
    $customToken = $auth->createCustomToken($firebaseUser);

    // اینجا نقش رو از Firestore یا دیتابیس خودت بگیر (الان موقتی admin می‌زنیم)
    $role = 'admin';

    echo json_encode([
        'success' => true,
        'token' => $customToken->toString(),
        'email' => $email,
        'uid' => $firebaseUser,
        'role' => $role
    ]);
} catch (\Kreait\Firebase\Exception\Auth\AuthError $e) {
    echo json_encode([
        'success' => false,
        'message' => '❌ ایمیل یا رمز اشتباه است.'
    ]);
}

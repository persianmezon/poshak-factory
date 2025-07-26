<?php
// api/firebase-test.php
$url = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=AIzaSyASe7763aembdzd_hJM2pGOiIF57EBTSpU';

$data = [
  'email' => 'testuser'.rand(1000,9999).'@example.com',
  'password' => '123456',
  'returnSecureToken' => true
];

$options = [
  'http' => [
    'method'  => 'POST',
    'header'  => "Content-type: application/json\r\n",
    'content' => json_encode($data)
  ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
  echo "❌ اتصال به Firebase از سمت هاست ناموفق بود.";
} else {
  echo "✅ اتصال موفق بود. پاسخ:\n";
  echo $result;
}
?>

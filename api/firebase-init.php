<?php
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;

$factory = (new Factory)
    ->withServiceAccount(__DIR__ . '/serviceAccountKey.json')
    ->withDatabaseUri('https://poshak-factory-default-rtdb.firebaseio.com/'); // ✅ اصلاح این خط

$auth = $factory->createAuth();
$database = $factory->createDatabase();

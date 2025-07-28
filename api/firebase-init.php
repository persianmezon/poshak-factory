<?php
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

$factory = (new Factory)
  ->withServiceAccount(__DIR__ . '/serviceAccountKey.json')
  ->withDatabaseUri('https://poshak-factory-default-rtdb.firebaseio.com/');

$database = $factory->createDatabase();

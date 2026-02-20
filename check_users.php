<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \Illuminate\Support\Facades\DB::table('users')->select('id', 'name', 'email', 'role')->get();
echo json_encode($users, JSON_PRETTY_PRINT);

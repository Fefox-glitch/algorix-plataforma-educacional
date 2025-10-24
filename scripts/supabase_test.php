<?php
require __DIR__ . '/../config.php';
$r = supabaseRequest('GET','computer_labs?select=id,name,location,capacity,is_active&limit=5');
header('Content-Type: application/json');
echo json_encode($r);
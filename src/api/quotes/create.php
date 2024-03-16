<?php

use config\Database;
use models\Quote;

$db = new Database();
$conn = $db->connect();

$quote = new Quote($conn);
$data = (array) json_decode(file_get_contents('php://input'));
echo $quote->create($data);

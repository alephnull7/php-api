<?php

use config\Database;
use models\Quote;

$db = new Database();
$conn = $db->connect();

$quote = new Quote($conn);
echo $quote->read_single($_GET);

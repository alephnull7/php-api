<?php

use config\Database;
use models\Author;

$db = new Database();
$conn = $db->connect();

$author = new Author($conn);
echo $author->read();

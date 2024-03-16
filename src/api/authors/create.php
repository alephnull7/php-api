<?php

use config\Database;
use models\Author;

$db = new Database();
$conn = $db->connect();

$author = new Author($conn);
$data = (array) json_decode(file_get_contents('php://input'));
echo $author->create($data);

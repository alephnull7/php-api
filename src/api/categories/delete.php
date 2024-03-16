<?php

use config\Database;
use models\Category;

$db = new Database();
$conn = $db->connect();

$category = new Category($conn);
$data = (array) json_decode(file_get_contents('php://input'));
echo $category->delete($data);

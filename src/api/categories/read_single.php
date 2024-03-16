<?php

use config\Database;
use models\Category;

$db = new Database();
$conn = $db->connect();

$category = new Category($conn);
echo $category->read_single($_GET);

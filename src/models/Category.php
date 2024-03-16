<?php

namespace models;

include_once 'Model.php';

class Category extends Model
{
    public function __construct($conn)
    {
        $name = 'category';
        parent::__construct($conn, $name);
    }
}

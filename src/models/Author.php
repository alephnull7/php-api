<?php

namespace models;

include_once 'Model.php';

class Author extends Model
{
    public function __construct($conn)
    {
        $name = 'author';
        parent::__construct($conn, $name);
    }
}

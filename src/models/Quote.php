<?php

namespace models;

include_once 'Model.php';

class Quote extends Model
{
    public function __construct($conn)
    {
        $name = 'quote';
        parent::__construct($conn, $name);
    }

    protected function set_cols()
    {
        parent::set_cols();
        $foreign_keys = array('author_id', 'category_id');
        switch ($this->operation) {
            case 'read_single':
                $this->diff_threshold = 2;
            case 'create':
            case 'update':
                $this->cols = array_merge($this->cols, $foreign_keys);
                break;
            default:
        }
    }

    protected function set_id_name()
    {
        $this->id_name = ucwords($this->table);
    }
}

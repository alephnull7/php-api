<?php

namespace models;

include_once 'Model.php';

class Quote extends Model
{
    private $random;

    public function __construct($conn)
    {
        $name = 'quote';
        parent::__construct($conn, $name);
    }

    public function read_single($data)
    {
        $this->set_random($data);
        $results = parent::read_single($data);
        if ($this->random) {
            return $this->get_random_result($results);
        } else {
            return $results;
        }
    }

    protected function set_cols()
    {
        parent::set_cols();
        $foreign_keys = array('author_id', 'category_id');
        switch ($this->operation) {
            case 'read_single':
                $this->diff_threshold = 3;
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

    private function set_random($data) {
        foreach ($data as $key => $val) {
            if ($key == 'random' && $val == 'true') {
                $this->random = TRUE;
                return;
            }
        }
        $this->random = FALSE;
    }

    private function get_random_result($results) {
        $data = json_decode($results)->data;
        $count = count($data);
        $index = rand(0, $count-1);
        return json_encode($data[$index]);
    }
}

<?php

namespace models;

include_once 'Model.php';

class Quote extends Model
{
    private $random;

    public function __construct($conn)
    {
        $name = 'quote';
        $foreign_keys = array('author_id', 'category_id');
        parent::__construct($conn, $name, $foreign_keys);
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
        switch ($this->operation) {
            case 'read_single':
                $this->diff_threshold++;
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
        $results = json_decode($results);
        if (isset($results->data)) {
            $data = $results->data;
            $count = count($data);
            $index = rand(0, $count-1);
            return json_encode($data[$index]);
        } else {
            return json_encode($results);
        }
    }
}

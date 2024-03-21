<?php

namespace models;
use PDO;

class Model
{
    // fields
    protected $conn;
    protected $table;
    protected $name;
    protected $id_name;
    protected $foreign_keys;
    protected $cols;
    protected $pars;
    protected $diff_threshold;
    protected $query;
    protected $stmt;
    protected $operation;
    protected $error;

    // constructor
    public function __construct($conn, $name, $foreign_keys = array())
    {
        $this->conn = $conn;
        $this->foreign_keys = $foreign_keys;
        $this->set_schema($name);
    }

    // public methods for api
    public function create($data)
    {
        $this->set_operation('create');
        $this->set_pars($data);
        $this->check_request();

        $this->set_query();
        $this->set_stmt();
        $this->bind_params();
        return $this->execute_stmt();
    }

    public function delete($data)
    {
        $this->set_operation('delete');
        $this->set_pars($data);
        $this->check_request();

        $this->set_query();
        $this->set_stmt();
        $this->bind_params();
        return $this->execute_stmt();
    }

    public function read()
    {
        $this->set_operation('read');
        $this->set_query();
        $this->set_stmt();
        $this->execute_stmt();

        $this->check_existence();
        return $this->get_json_result();
    }

    public function read_single($data)
    {
        $this->set_operation('read_single');
        $this->set_pars($data);
        $this->check_parameters();

        $this->set_query();
        $this->set_stmt();
        $this->bind_params();
        $this->execute_stmt();

        $this->check_existence();
        return $this->get_json_result();
    }

    public function update($data)
    {
        $this->set_operation('update');
        $this->set_pars($data);
        $this->check_request();

        $this->set_query();
        $this->set_stmt();
        $this->bind_params();
        return $this->execute_stmt();
    }

    // protected methods overridden by subclasses
    protected function set_cols()
    {
        $this->diff_threshold = 0;
        switch ($this->operation) {
            case 'read_single':
                $this->diff_threshold = count($this->foreign_keys);
            case 'delete':
                $this->cols = array('id');
                $this->cols = array_merge($this->cols, $this->foreign_keys);
                break;
            case 'create':
                $this->cols = array($this->name);
                $this->cols = array_merge($this->cols, $this->foreign_keys);
                break;
            case 'update':
                $this->cols = array('id', $this->name);
                $this->cols = array_merge($this->cols, $this->foreign_keys);
                break;
            default:
                $this->cols = NULL;
        }
    }

    protected function set_id_name()
    {
        $this->id_name = "{$this->name}_id";
    }

    // private class methods
    private function check_request()
    {
        $this->check_parameters();
        $this->check_existences();
    }

    private function check_parameters()
    {
        if (is_null($this->pars)) {
            $this->parameter_error();
            echo $this->error_message();
            exit();
        }
    }

    private function check_existences()
    {
        // save for when we return from other operation(s)
        $name = $this->name;
        $operation = $this->operation;
        $pars = $this->pars;
        $foreign_keys = $this->foreign_keys;

        // check each key separately for more informative messages
        foreach ($this->pars as $key => $val) {
            $key_arr = explode("_", $key);
            if (!in_array('id', $key_arr)) {
                continue;
            }

            if ($key != 'id') {
                $this->table = getenv(strtoupper($key_arr[0]));
                $this->id_name = $key;
                $this->foreign_keys = array();
            }
            $data = array('id' => $val);

            $this->read_single($data);
            $this->check_existence();

            // reset model state
            $this->foreign_keys = $foreign_keys;
            $this->set_schema($name);
            $this->set_operation($operation);
            $this->pars = $pars;
        }
    }

    private function check_existence()
    {
        if ($this->row_count() == 0) {
            $this->existence_error();
            echo $this->error_message();
            exit();
        }
    }

    private function set_pars($data)
    {
        $data = $this->clean_data($data);
        $pars = array();
        foreach ($data as $key => $val) {
            if (in_array($key, $this->cols)) {
                $pars[$key] = $val;
            }
        }

        // check we have the needed parameters
        $diff = array_diff($this->cols, array_keys($pars));
        if (count($diff) <= $this->diff_threshold) {
            $this->pars = $pars;
        } else {
            $this->pars = NULL;
        }
    }

    private function set_schema($name)
    {
        $this->name = $name;
        $this->table = getenv(strtoupper($name));
        $this->set_id_name();
    }

    private function set_stmt()
    {
        $this->stmt = $this->conn->prepare($this->query);
    }

    private function bind_params()
    {
        foreach (array_keys($this->pars) as $par) {
            $this->bind_param($par);
        }
    }

    private function bind_param($par)
    {
        $placeholder = ':' . $par;
        $this->stmt->bindParam($placeholder, $this->pars[$par]);
    }

    private function execute_stmt()
    {
        if ($this->stmt->execute()) {
            $this->update_pars();
            return json_encode(
                $this->pars
            );
        } else {
            $message = sprintf("Error: %s.\n", $this->stmt->error);
            print($message);
            return json_encode(
                array('message' => $message)
            );
        }
    }

    private function row_count()
    {
        return $this->stmt->rowCount();
    }

    private function get_json_result()
    {
        $data_arr = array();
        while ($row = $this->stmt->fetch(PDO::FETCH_ASSOC)) {
            $data_arr[] = $row;
        }

        return json_encode($data_arr);
    }

    private function update_pars()
    {
        switch ($this->operation) {
            case 'create':
                $this->pars['id'] = $this->conn->lastInsertId();
                break;
            default:
        }
    }

    private function parameter_error()
    {
        $this->error = 'parameter';
    }

    private function existence_error()
    {
        $this->error = 'existence';
    }

    private function error_message()
    {
        switch ($this->error) {
            case 'existence':
                if ($this->id_name == ucwords($this->table)) {
                    $message = "No {$this->id_name} Found";
                } else {
                    $message = "{$this->id_name} Not Found";
                }
                break;
            case 'parameter':
                $message = "Missing Required Parameters";
                break;
            default:
                $message = "Request Not Fulfilled";
        }
        return json_encode(
            array('message' => $message)
        );
    }

    private function set_operation($operation)
    {
        $this->operation = $operation;
        $this->set_cols();
    }

    private function clean_data($data)
    {
        foreach ($data as $key => $val) {
            $data[$key] = htmlspecialchars(strip_tags($val));
        }
        return $data;
    }

    private function set_query()
    {
        switch ($this->operation) {
            case 'create':
                $this->set_query_create();
                break;
            case 'delete':
                $this->set_query_delete();
                break;
            case 'read':
                $this->set_query_read();
                break;
            case 'read_single':
                $this->set_query_read_single();
                break;
            case 'update':
                $this->set_query_update();
                break;
            default:
        }
//        echo $this->query;
    }

    private function set_query_create()
    {
        $cols = array_diff($this->cols, array('id'));
        $cols_str = implode(', ', $cols);
        $vals_str = ':' . implode(', :', $cols);
        $this->query = "INSERT INTO {$this->table} ({$cols_str}) VALUES ({$vals_str})";
    }

    private function set_query_delete()
    {
        $this->query = "DELETE FROM {$this->table} WHERE id = :id";
    }

    private function set_query_read()
    {
        $this->query = "SELECT {$this->all_select()} FROM {$this->table} {$this->join_substring()}ORDER BY id";
    }

    private function set_query_read_single()
    {
        $where_sub = $this->query_substring("WHERE");
        $this->query = "SELECT {$this->all_select()} FROM {$this->table}{$this->join_substring()}{$where_sub} ORDER BY id";
    }

    private function set_query_update()
    {
        $set_sub = $this->query_substring("SET");
        $this->query = "UPDATE {$this->table}{$set_sub} WHERE id = :id";
    }

    protected function query_substring($type)
    {
        $par_keys = array_keys($this->pars);
        if (count($par_keys) == 0) {
            return "";
        }

        switch ($type) {
            case 'WHERE':
                $delim = "AND";
                break;
            case 'SET':
                $delim = ",";
                $par_keys = array_diff($par_keys, array('id'));
                break;
            default:
                $delim = "";
        }
        $par_keys = array_values($par_keys);

        $substring = " " . $type . " ";
        $delim = " " . $delim . " ";
        for ($index = 0; $index < count($par_keys); $index++) {
            $col = $par_keys[$index];
            $substring = $substring . "{$this->table_prefix()}{$col} = :{$col}";
            if ($index < count($par_keys) - 1) {
                $substring = $substring . $delim;
            }
        }
        return $substring;
    }

    private function all_select()
    {
        if (count($this->foreign_keys) == 0) {
            return "*";
        }

        $cols = $this->cols;
        $cols[] = $this->name;
        $cols[] = "id";
        $cols = array_merge($cols, $this->foreign_keys);
        $cols = array_unique($cols);
        $all_select = "";
        for ($index = 0; $index < count($cols); $index++) {
            $col = $cols[$index];
            $val_arr = explode("_", $col);

            if (in_array("id", $val_arr) && count($val_arr) > 1) {
                $name = $val_arr[0];
                $joined_table = getenv(strtoupper($name));
                $all_select = $all_select . "{$joined_table}.{$name} AS {$name}";
            } else {
                $all_select = $all_select . "{$this->table}.{$col} AS {$col}";
            }

            if ($index < count($cols) - 1) {
                $all_select = $all_select . ", ";
            }
        }
        return $all_select;
    }

    protected function join_substring() {
        if (count($this->foreign_keys) > 0) {
            $join_sub = " ";
        } else {
            $join_sub = "";
        }

        foreach ($this->foreign_keys as $val) {
            $val_arr = explode("_", $val);
            $joined_table = getenv(strtoupper($val_arr[0]));
            $join_sub = $join_sub . "LEFT JOIN {$joined_table} ON {$this->table}.{$val} = {$joined_table}.id ";
        }
        return $join_sub;
    }

    protected function table_prefix() {
        if (count($this->foreign_keys) > 0) {
            $table_prefix = "{$this->table}.";
        } else {
            $table_prefix = "";
        }
        return $table_prefix;
    }
}

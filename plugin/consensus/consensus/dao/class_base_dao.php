<?php

abstract class DaoBase {

    private DB_Base $db;

    public function __construct(DB_Base $db) {
        $this->db = $db;
    }

    protected function escape_input($data) {
        $escape_data = null;
        foreach ($data as $key => $value) {
            $escape_data[$this->db->escape_string($key)] = $this->db->escape_string($value);
        }
        return $escape_data;
    }

}

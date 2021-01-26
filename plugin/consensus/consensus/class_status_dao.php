<?php


class StatusDao
{
    private $db;

    public function __construct(DB_Base $db) {
        $this->db = $db;
    }

    /**
     * @param $status
     * @return Status
     */
    public function find_status_by_name($status) {
        $query = $this->db->simple_select('consensus_status', '*', "status='{$status}'");
        $row = $this->db->fetch_array($query);

        require_once MYBB_ROOT . 'inc/plugins/consensus/models/class_status.php';
        return new Status($row['status_id'], $row['status']);
    }

}
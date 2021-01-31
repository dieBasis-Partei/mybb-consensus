<?php

class ConsensusDbTables {

    private $db;
    private $databaseType;

    // Table names
    private $consensus = "consensus";
    private $status = "consensus_status";
    private $proposals = "consensus_proposals";
    private $votes = "consensus_votes";

    private $tables;

    public function __construct(DB_Base $db, $databaseType) {
        $this->db = $db;
        $this->databaseType = $databaseType;

        $this->tables = array($this->consensus, $this->status,
                $this->proposals, $this->votes);
    }

    public function check_tables_exists($require_all) {
        if ($require_all) {
            $exists = true;
            for($i = 0; $i < sizeof($this->tables) && $exists; $i++) {
                $exists = $this->db->table_exists($this->tables[$i]);
            }
            return $exists;
        } else {
            $exists = false;
            for($i = 0; $i < sizeof($this->tables) && !$exists; $i++) {
                $tbl_exists = $this->db->table_exists($this->tables[$i]);
                $exists = !$exists ? $tbl_exists : $exists;
            }
            return $exists;
        }
    }

    public function install() {
        if ($this->check_tables_exists(true)) {
            return;
        }
        echo "type: ".$this->databaseType;

        switch ($this->databaseType) {
            case "pgsql":
                $this->create_postgres_tables();
                break;
            case "sqlite":
                // TODO: Implement sqlite tables
                $this->create_sqlite_tables();
                break;
            default:
                // TODO: Implement default sql tables
                $this->create_sql_tables($this->db->build_create_table_collation());
                break;
        }

        $this->insert_default_data();
        $this->insert_icons();
    }

    private function create_postgres_tables()
    {
        // Create consensus_status table
        $this->db->write_query("CREATE TABLE ".TABLE_PREFIX.$this->status." (
            status_id serial,
            status varchar(40) NOT NULL,
            PRIMARY KEY(status_id)
        );");

        // Create consensus table
        $this->db->write_query("CREATE TABLE ".TABLE_PREFIX.$this->consensus." (
            consensus_id serial,
            title varchar(255) NOT NULL,
            description text,
            expires timestamp NOT NULL,
            created timestamp NOT NULL default NOW(),
            creator INTEGER NOT NULL,
            thread_id INTEGER NOT NULL,
            status serial,
            PRIMARY KEY (consensus_id),
            CONSTRAINT fk_user_id
                FOREIGN KEY (creator)
                REFERENCES ".TABLE_PREFIX."users(uid),
            CONSTRAINT fk_status
                FOREIGN KEY (status)
                REFERENCES ".TABLE_PREFIX.$this->status."(status_id),
            CONSTRAINT fk_thread
                FOREIGN KEY(thread_id)
                REFERENCES ".TABLE_PREFIX."threads(tid)
        );");

        // Create proposal table
        $this->db->write_query("CREATE TABLE ".TABLE_PREFIX.$this->proposals." (
            proposal_id serial,
            title varchar(255) NOT NULL,
            description text,
            position INTEGER,
            consensus_id serial,
            PRIMARY KEY (proposal_id),
            CONSTRAINT fk_consensus
                FOREIGN KEY (consensus_id)
                REFERENCES ".TABLE_PREFIX.$this->consensus."(consensus_id)
        );");

        // Create votes table
        $this->db->write_query("CREATE TABLE ".TABLE_PREFIX.$this->votes." (
            vote_id serial,
            proposal_id serial,
            user_id INTEGER,
            points smallint,
            PRIMARY KEY(vote_id),
            CONSTRAINT fk_proposal
                FOREIGN KEY(proposal_id)
                REFERENCES ".TABLE_PREFIX.$this->proposals."(proposal_id),
            CONSTRAINT fk_user_id
                FOREIGN KEY(user_id)
                REFERENCES ".TABLE_PREFIX."users(uid)        
        );");
    }

    private function create_sqlite_tables()
    {
        // TODO: Implement me.
    }

    private function create_sql_tables($collation)
    {
        // TODO: Implement me.
    }

    private function insert_default_data()
    {
        $this->db->insert_query($this->status, array('status' => 'active'));
        $this->db->insert_query($this->status, array('status' => 'closed'));
        $this->db->insert_query($this->status, array('status' => 'inactive'));
        $this->db->insert_query($this->status, array('status' => 'expired'));
    }

    private function insert_icons() {
        $this->db->insert_query('icons', array('name' => 'Consensus', 'path' => 'images/icons/consensus.png'));
    }

    public function uninstall() {
        if (!$this->check_tables_exists(false)) {
            return;
        }

        $this->db->drop_table($this->votes, true);
        $this->db->drop_table($this->proposals, true);
        $this->db->drop_table($this->consensus, true);
        $this->db->drop_table($this->status, true);

        $this->db->delete_query('icons', "name LIKE '%Consensus%'");
    }

    public function consensus_active($thread_id)
    {
        $query = $this->db->simple_select($this->consensus, 'status', "thread_id='{$thread_id}'", array("limit" => 1));
        $status_id = $this->db->fetch_field($query, 'status');

        if($status_id) {
            $status_row = $this->find_status(null, $status_id);
            return $status_row ? $status_row['status'] == 'active' : false;
        }
        return false;
    }

    private function find_status($status, $status_id) {
        global $db;

        if (strlen($status)) {
            $query = $db->simple_select('consensus_status', '*', "status='{$status}'");
            return $db->fetch_array($query);
        } else {
            $query = $db->simple_select('consensus_status', '*', "status_id='{$status_id}'");
            return $db->fetch_array($query);
        }
    }

}
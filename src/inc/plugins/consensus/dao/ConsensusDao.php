<?php
namespace mybb\consensus\dao;

use mybb\consensus\models\Consensus;

class ConsensusDao
{
    private $db;

    public function __construct(DB_Base $db) {
        $this->db = $db;
    }

    public function insert(Consensus $consensus) {
        $consensus_id = $this->db->insert_query('consensus', $consensus->toDBArray());
        if ($consensus_id > 0) {
            $proposals = $consensus->getProposals();
            foreach ($proposals as $proposal) {
                $proposal->setConsensusId($consensus_id);
                $this->db->insert_query('consensus_proposals', $proposal->toDBArray());
            }
            return true;
        }
        return false;
    }

    public function find_by_thread_id($thread_id) {
        $tid = $this->db->escape_string($thread_id);

        $query = $this->db->simple_select('consensus', '*', "thread_id='{$tid}'");
        $db_obj = $this->db->fetch_array($query);

        if (!$db_obj) {
            return null;
        }

        $consensus_id = $db_obj['consensus_id'];
        $title = $db_obj['title'];
        $description = $db_obj['description'];
        $expires = DateTime::createFromFormat("Y-m-d H:i:s", $db_obj['expires']);
        $creator_id = $db_obj['creator'];
        $cthread_id = $db_obj['thread_id'];
        $status_id = $db_obj['status'];

        // Get all proposals considering to consensus
        $squery = $this->db->query("SELECT * FROM ".TABLE_PREFIX."consensus_proposals WHERE consensus_id='{$consensus_id}' ORDER BY position ASC");

        $proposals = array();
        while($result = $this->db->fetch_array($squery)) {
            $proposals[] = new Proposal($result['title'], $result['description'], $result['position'], $result['consensus_id'], $result['proposal_id']);
        }

        return new Consensus($title, $description, $expires, $creator_id, $cthread_id, $status_id, $proposals, $consensus_id);
    }

    public function update_status($consensus_id, $status_id) {
        $this->db->update_query('consensus', ["status" => $status_id], "consensus_id='{$consensus_id}'");
    }

    public function has_thread_consensus($thread_id) {
        $tid = $this->db->escape_string($thread_id);
        $query = $this->db->simple_select('consensus', 'COUNT(consensus_id) AS count', "thread_id='{$tid}'");
        $count = $this->db->fetch_field($query, 'count');
        return $count > 0;
    }

    public function delete($consensus_id) {
        // TODO: Needs to be implemented.
        return true;
    }

}
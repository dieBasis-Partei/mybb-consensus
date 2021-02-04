<?php
require_once(MYBB_ROOT . 'inc/plugins/consensus/models/class_vote.php');

class VoteDao
{
    private $db;

    public function __construct(DB_Base $db) {
        $this->db = $db;
    }

    public function insert(Vote $vote) {
        return $this->db->insert_query('consensus_votes', $vote->toDBArray()) > 0;
    }

    public function find_vote($user_id, $proposal_id) {
        $user_id = $this->db->escape_string($user_id);
        $proposal_id = $this->db->escape_string($proposal_id);
        $result = $this->db->simple_select('consensus_votes', '*', "user_id='{$user_id}' AND proposal_id='{$proposal_id}'", array('limit' => '1'));


        $row = $this->db->fetch_array($result);
        if ($row != null) {
            return new Vote($row['proposal_id'], $row['user_id'], $row['points'], $row['vote_id']);
        }

        return null;
    }

    public function find_votes_by_proposal_id($proposal_id) {
        $query = $this->db->simple_select('consensus_votes', '*', "proposal_id='{$proposal_id}'");

        $votes = array();
        while($result = $this->db->fetch_array($query)) {
            $votes[] = new Vote($result['proposal_id'], $result['user_id'], $result['points'], $result['vote_id']);
        }
        return $votes;
    }

}
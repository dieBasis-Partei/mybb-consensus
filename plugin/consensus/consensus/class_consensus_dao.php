<?php


class ConsensusDao
{
    private $db;

    public function __construct(DB_Base $db) {
        $this->db = $db;
    }

    public function insert(Consensus $consensus) {
        $consensus_id = $this->db->insert_query('consensus', $consensus->toDBArray());
        if ($consensus_id > 0) {
            $suggestions = $consensus->getSuggestions();
            foreach ($suggestions as $suggestion) {
                $suggestion->setConsensusId($consensus_id);
                $this->db->insert_query('consensus_questions', $suggestion->toDBArray());
            }
            return true;
        }
        return false;
    }

    public function delete($consensus_id) {

        return true;
    }

}
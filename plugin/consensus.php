<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

// cache templates - this is important when it comes to performance
// THIS_SCRIPT is defined by some of the MyBB scripts, including index.php
if(defined('THIS_SCRIPT'))
{
    global $templatelist;

    if(isset($templatelist))
    {
        $templatelist .= ',';
    }

    if(THIS_SCRIPT== 'showthread.php')
    {
        $templatelist .= 'consensus_create_form';
    }
}

require_once (MYBB_ROOT.'inc/plugins/consensus/models/class_status.php');
require_once (MYBB_ROOT . 'inc/plugins/consensus/class_consensus_database.php');
require_once (MYBB_ROOT . 'inc/plugins/consensus/class_consensus_templates.php');
require_once (MYBB_ROOT.'inc/plugins/consensus/dao/class_consensus_dao.php');
require_once (MYBB_ROOT.'inc/plugins/consensus/dao/class_vote_dao.php');


if (defined('IN_ADMINCP'))
{
    // Add our hello_settings() function to the setting management module to load language strings.
    // TODO: Add hooks for settings here
    // We could hook at 'admin_config_settings_begin' only for simplicity sake.
}
else
{
    // Add our hello_index() function to the index_start hook so when that hook is run our function is executed
    $plugins->add_hook('showthread_start', 'consensus_showthread');

    $plugins->add_hook('forumdisplay_thread', 'consensus_thread_caption');

    // Add our hello_post() function to the postbit hook so it gets executed on every post
    //$plugins->add_hook('postbit', 'hello_post');

    // Add our hello_new() function to the misc_start hook so our misc.php?action=hello inserts a new message into the created DB table.
    $plugins->add_hook('misc_start', 'consensus_handle_action_data');
}

const TWO_WEEKS = 14 * 24 * 60 * 60;
const DATE_FORMAT = 'd.m.Y H:i:s';

$consensusDbTables = null;
$consensusTemplates = null;

function consensus_info()
{
    $codename = str_replace('.php', '', basename(__FILE__));
    return array(
            "name"			=> "MyBB-consensus",
            "description"	=> "A plugin for creating consensus polls in MyBB",
            "website"		=> "https://github.com/dieBasis-Partei/mybb-consensus",
            "author"		=> "dieBasis",
            "authorsite"	=> "https://www.diebasis-partei.de",
            "version"		=> "0.1",
            "guid" 			=> time(),
            "codename"		=> $codename,
            "compatibility" => "*" // TODO
    );
}

function __init()
{
    global $db, $consensusDbTables, $consensusTemplates;

    $consensusDbTables = new ConsensusDbTables($db, $db->type);
    $consensusTemplates = new ConsensusTemplates($db);
}

function consensus_install() {
    __init();
    global $consensusTemplates, $consensusDbTables, $mybb;
    $consensusDbTables->install();
    $consensusTemplates->install();

    // update icon cache.
    $mybb->cache->update_posticons();
}

function consensus_is_installed() {
    __init();
    global $consensusDbTables;
    return null != $consensusDbTables && $consensusDbTables->check_tables_exists(true);
}

function consensus_uninstall() {
    __init();
    global $consensusDbTables, $consensusTemplates;
    $consensusTemplates->uninstall();
    $consensusDbTables->uninstall();
}

function consensus_activate() {
    __init();
    global $consensusTemplates;
    $consensusTemplates->activate_templates();
}

function consensus_deactivate() {
    __init();
    global $consensusTemplates;
    $consensusTemplates->deactivate_templates();
}

// Display the consensus poll in thread
function consensus_showthread() {
    global $mybb, $db, $consensusbox, $newconsensus;
    $thread_id = $mybb->input['tid'];

    $consensus_dao = new ConsensusDao($db);
    $consensus = $consensus_dao->find_by_thread_id($thread_id);

    if (null == $consensus) {
        $consensusbox = '';
        $newconsensus = is_mod() ? '<a href="new_consensus.php?action=new_consensus&tid='.$thread_id.'" class="button">neue Konsensierung</a>' : '';
    } else {
        $closed = is_consensus_closed($consensus);
        if (is_consensus_expired($consensus->getExpires()) || $closed) {
            $expired_status_id = find_status_id(Status::STATUS_EXPIRED);
            if ($consensus->getStatusId() != $expired_status_id && !$closed) {
                $consensus_dao->update_status($consensus->getConsensusId(), $expired_status_id);
            }

            display_results($consensus);
        } else {
            display_poll($consensus);
        }
    }
}

function is_mod() {
    global $mybb;

    $user = $mybb->user;
    return $user['ismoderator'] == null ? false : true;
}

function is_consensus_expired(DateTime $expiry_date) {
    $current_date = new DateTime();
    return $current_date > $expiry_date;
}

function is_consensus_closed($consensus) {
    return $consensus->getStatusId() == find_status_id(Status::STATUS_CLOSED);
}

function display_results($consensus) {
    global $consensusbox, $db, $templates, $lang;
    $lang->load('consensus');

    $consensus_closed = $consensus->getStatusId() == find_status_id(Status::STATUS_CLOSED) ? $lang->consensus_status_closed_note : '';

    $vote_dao = new VoteDao($db);

    $proposals = $consensus->getProposals();
    foreach($proposals as $proposal) {
        $resistance_points = $i;
        $votes = $vote_dao->find_votes_by_proposal_id($proposal->getId());

        if (sizeof($votes) > 0) {
            $vote_data = calculate_results($votes);

            $summary_total_points = $vote_data['points'];
            $summary_total_votes = $vote_data['total_votes'];
            $summary_no_votes = $vote_data['invalid_votes'];

            if ($summary_total_votes == 0) {
                $summary_total_acceptance_percent = '-/-';
                $summary_total_acceptance_percent_rounded = '-/-';
                $summary_total_votes = '-/-';
                $proposal_progress_size = 50;
            } else {
                $summary_total_acceptance_percent = (100 - (($summary_total_points / $summary_total_votes) * 10));
                $summary_total_acceptance_percent_rounded = round($summary_total_acceptance_percent, 2);
                $proposal_progress_size = 50 - ($summary_total_acceptance_percent * 0.5);
            }
            $proposal_summary_results = eval($templates->render('consensus_display_results_summary'));
            $proposal_results = eval($templates->render('consensus_display_results_proposal_results'));

            $results .= eval($templates->render('consensus_display_results'));

            // Reset all data.
            $summary_total_acceptance_percent = $proposal_progress_size = $summary_no_votes = $summary_total_points = $summary_no_votes = $summary_total_acceptance_percent = $summary_total_acceptance_percent_rounded = null;
        }
    }

    $consensusbox = eval($templates->render('consensus_display_consensus'));
}

function calculate_results($votes) {
    // 100% = 50em
    $results = array();
    $points = 0;
    $num_of_votes = 0;
    $invalid_votes = 0;
    foreach ($votes as $vote) {
        if ($vote->getPoints() >= 11) {
            $invalid_votes += 1;
        } else {
            $points += $vote->getPoints();
            $num_of_votes += 1;
        }
    }

    return ['points' => $points, 'invalid_votes' => $invalid_votes, 'total_votes' => $num_of_votes];
}


function display_poll(Consensus $consensus) {
    global $templates, $lang, $mybb, $db, $consensusbox, $newconsensus;
    $lang->load('consensus');

    $user_id = $mybb->user['uid'];
    $vote_dao = new VoteDao($db);

    $proposals = "";
    $proposals_list = $consensus->getProposals();
    $read_mode = '';
    foreach($proposals_list as $proposal) {
        $vote = $vote_dao->find_vote($user_id, $proposal->getId());
        $read_mode = $read_mode == '' && $vote != null ? 'disabled' : '';

        $readonly = $vote == null ? '' : 'readonly';
        $points_metric = "";
        for($i = 0; $i <= 11; $i++) {
            $checked = ($vote != null && $i == $vote->getPoints()) ? 'checked' : '';
            $disabled = ($vote != null) ? 'disabled' : '';
            $resistance_points = $i;
            $resistance_points_label = $i == 11 ? $lang->consensus_proposal_caption_no_opinion : $i;
            $points_metric .= eval($templates->render('consensus_display_form_proposal_points'));
        }
        $proposals .= eval($templates->render('consensus_display_form_proposal'));
    }

    $expiry = $lang->consensus_expires_at." ".$consensus->getExpires()->format("d.m.Y H:i");
    $number_of_proposals = count($proposals_list);
    $close_consensus = $mybb->user['uid'] == $consensus->getUserId() ? eval($templates->render('consensus_close_form_consensus')) : '';
    $consensusbox = eval($templates->render('consensus_display_form_consensus'));
    $newconsensus = '';
}

function consensus_handle_action_data() {
    global $mybb;

    $consensus_post_code = $mybb->get_input('consensus_post_code');
    verify_post_check($consensus_post_code);

    $action = $mybb->get_input('action');
    if ($action == 'consensus_vote') {
        consensus_save_vote();
    }
    if ($action == 'consensus_close') {
        consensus_close();
    }

    // TODO: Better error handling.
    error_no_permission();
}

function consensus_save_vote() {
    global $db, $mybb, $lang;
    $lang->load('consensus');

    $thread_id = $mybb->get_input('thread_id');
    $consensus_id = $mybb->get_input('consensus_id');
    $user_id = $mybb->user['uid'];
    $consensus_proposals_size = $mybb->get_input('consensus_proposals_size');

    $votes = [];
    for($i = 1; $i <= $consensus_proposals_size; $i++) {
        $proposal_id = $mybb->get_input('proposal_'.$i);
        $points = $mybb->get_input('proposal_points_'.$i);
        $votes[] = new Vote($proposal_id, $user_id, $points);
    }

    $vote_dao = new VoteDao($db);
    foreach ($votes as $vote) {
        $vote_dao->insert($vote);
    }
    redirect('showthread.php?tid='.$thread_id, $lang->consensus_vote_successful);
}

function consensus_close() {
    global $db, $mybb, $lang;

    $thread_id = $mybb->get_input('thread_id');
    $consensus_id = $mybb->get_input('consensus_id');
    $consensus_dao = new ConsensusDao($db);
    $consensus_dao->update_status($consensus_id, find_status_id(Status::STATUS_CLOSED));
    redirect('showthread.php?tid='.$thread_id, $lang->consensus_closed_successfully);
}

function consensus_thread_caption() {
    global $thread, $db, $lang;
    $icon_consensus = find_icon_by_name('Consensus');
    $icon_consensus_id = $icon_consensus != null ? $icon_consensus['iid'] : 0;

    $lang->load('consensus');
    $consensus_dao = new ConsensusDao($db);
    if ($consensus_dao->has_thread_consensus($thread['tid'])) {
        $thread['icon'] = $icon_consensus_id;
    }
}

function find_icon_by_name($icon_name) {
    global $mybb;

    $icon_cache = $mybb->cache->read('posticons');

    $searched_icon = null;
    foreach ($icon_cache as $icon) {
        if ($icon['name'] == $icon_name) {
            $searched_icon = $icon;
            break;
        }
    }
    return $searched_icon;
}

function find_status_id($status) {
    global $db;

    $query = $db->simple_select('consensus_status', 'status_id', 'status=\''.$status.'\'');
    return $db->fetch_field($query, 'status_id');
}

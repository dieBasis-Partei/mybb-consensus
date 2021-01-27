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
    //$plugins->add_hook('misc_start', 'hello_new');
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
            "version"		=> "0.1-SNAPSHOT-".date("d.m.Y-H:i:s", time()),
            "guid" 			=> time(),
            "codename"		=> $codename,
            "compatibility" => "*" // TODO
    );
}

function __init()
{
    global $db, $consensusDbTables, $consensusTemplates;
    require_once MYBB_ROOT . 'inc/plugins/consensus/class_consensus_database.php';
    $consensusDbTables = new ConsensusDbTables($db, $db->type);

    require_once MYBB_ROOT . 'inc/plugins/consensus/class_consensus_templates.php';
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

    require_once(MYBB_ROOT.'inc/plugins/consensus/dao/class_consensus_dao.php');
    $consensus_dao = new ConsensusDao($db);
    $consensus = $consensus_dao->find_by_thread_id($thread_id);

    if (null == $consensus) {
        $consensusbox = '';
        $newconsensus = '<a href="new_consensus.php?action=new_consensus&tid='.$thread_id.'">neue Konsensierung</a>';
    } else {
        global $templates, $lang;
        $lang->load('consensus');

        $proposals = "";
        $proposals_list = $consensus->getProposals();
        foreach($proposals_list as $proposal) {
            $points_metric = "";
            for($i = 0; $i <= 11; $i++) {
                $resistance_points = $i;
                $resistance_points_label = $i == 11 ? $lang->consensus_proposal_caption_no_opinion : $i;
                $points_metric .= eval($templates->render('consensus_display_form_proposal_points'));
            }
            $proposals .= eval($templates->render('consensus_display_form_proposal'));
        }

        $consensusbox = eval($templates->render('consensus_display_form_consensus'));
        $newconsensus = 'active consensus';
    }
}

function consensus_thread_caption() {
    global $thread, $db, $lang;
    $icon_consensus = find_icon_by_name('Consensus');
    $icon_consensus_id = $icon_consensus != null ? $icon_consensus['iid'] : 0;

    $lang->load('consensus');
    require_once(MYBB_ROOT.'inc/plugins/consensus/dao/class_consensus_dao.php');
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

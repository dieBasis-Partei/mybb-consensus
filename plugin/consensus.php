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
        $templatelist .= 'consensus_showthread';
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
    global $consensusTemplates, $consensusDbTables;
    $consensusDbTables->install();
    $consensusTemplates->install();
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
    __init();
    global $consensusDbTables, $mybb, $consensusbox, $newconsensus;
    $thread_id = $mybb->input['tid'];
    if (!$consensusDbTables->consensus_active($thread_id)) {
        $consensusbox = '';
        $newconsensus = '<a href="new_consensus.php?action=new_consensus&tid='.$thread_id.'">neue Konsensierung</a>';
    } else {
        global $templates, $lang;
        $lang->load('consensus');
        $consensusbox = eval($templates->render('consensus_showthread'));
        $newconsensus = '';
    }
}

// Create a new consensus poll
function consensus_new() {
    global $mybb;

    if ($mybb->get_input('action') != 'create_consensus') {
        return;
    }

    if($mybb->request_method != 'post') {
        error_no_permission();
    }

    global $lang;
    $lang->load('consensus');
    verify_post_check($mybb->get_input('consensus_post_key'));

    $thread_id = $mybb->get_input('tid');   // TODO: Validierung
    $title = trim($mybb->get_input('consensus_title'));
    if (!$title || my_strlen($title) > 255) {
        error($lang->consensus_title_not_valid);
    }

    $description = trim($mybb->get_input('consensus_description'));

    $expire_string = $mybb->get_input('consensus_expire');
    $expire = time() + TWO_WEEKS; // Default use two weeks for a consensus poll
    if ($expire_string) {
        $expireDate = DateTime::createFromFormat(DATE_FORMAT, $expire_string);
        $expire = $expireDate->getTimestamp();
    }
    $num_of_choices = $mybb->get_input('consensus_num_of_choices');

    if ($num_of_choices <= 0) {
        error($lang->consensus_error_no_choices);
    }

    $choices = array();
    for($i = 0; $i < $num_of_choices; $i++) {
        $choice = $mybb->get_input('consensus_choice_'.$i);
        if (!$choice || my_strlen($choice) > 255) {
            error($lang->consensus_choice_not_valid);
        }
        $choices[$i] = $choice;
    }

    create_consensus($title, $description, $thread_id, $expire, $choices);
}

function create_consensus($title, $description, $thread_id, $expiry, $choices) {
    global $db, $user, $lang;
    $lang->load('consensus');

    $status_active_id = find_status_id('active');
    $poll_id = $db->insert_query('consensus_polls', array(
            'title' => $title,
            'description' => $description,
            'expires' => $expiry,
            'created_at' => time(),
            'created_by' => $user['uid'],
            'thread_id' => $thread_id,
            'status' => $status_active_id));

    if ($poll_id <= 0) {
        error($lang->consensus_error_consensus_creation);
    }

    $insert_choices = array();
    foreach ($choices as $choice) {
        array_push($insert_choices, array("poll_id" => $poll_id, "choice" => $choice));
    }
    $db->insert_query_multiple('consensus_choices', $insert_choices);

    // Redirect to index.php with a message
    redirect('index.php', $lang->consensus_done);
}

function find_status_id($status) {
    global $db;

    $query = $db->simple_select('consensus_status', 'status_id', 'status=\''.$status.'\'');
    return $db->fetch_field($query, 'status_id');
}

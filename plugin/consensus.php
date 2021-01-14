<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

const TWO_WEEKS = 14 * 24 * 60 * 60;
const DATE_FORMAT = 'd.m.Y H:i:s';

function consensus_info()
{
    $codename = str_replace('.php', '', basename(__FILE__));
    return array(
            "name"			=> "MyBB-consensus",
            "description"	=> "A plugin for creating consensus polls in MyBB",
            "website"		=> "https://github.com/dieBasis-Partei/mybb-consensus",
            "author"		=> "dieBasis",
            "authorsite"	=> "https://www.diebasis-partei.de",
            "version"		=> "0.1-SNAPSHOT",
            "guid" 			=> "",
            "codename"		=> $codename,
            "compatibility" => "*" // TODO
    );
}

function consensus_install()
{
    create_tables();
}

function create_tables()
{
    global $db;

    if ($db->table_exists("consensus_polls")) {
        return;
    }

    // Create consensus_status table
    $db->write_query("CREATE TABLE ".TABLE_PREFIX."consensus_status (
        status_id serial,
        status varchar(40) NOT NULL,
        PRIMARY KEY(status_id)
    );");

    // Create consensus_polls table
    $db->write_query("CREATE TABLE ".TABLE_PREFIX."consensus_polls (
        poll_id serial,
        title varchar(255) NOT NULL,
        description text,
        expires timestamp NOT NULL,
        created_at timestamp NOT NULL default NOW(),
        created_by_user_id INTEGER NOT NULL,
        thread_id INTEGER NOT NULL,
        status serial,
        PRIMARY KEY (poll_id),
        CONSTRAINT fk_user_id
            FOREIGN KEY (created_by_user_id)
            REFERENCES ".TABLE_PREFIX."users(uid),
        CONSTRAINT fk_status
            FOREIGN KEY (status)
            REFERENCES ".TABLE_PREFIX."consensus_status(status_id),
        CONSTRAINT fk_thread
            FOREIGN KEY(thread_id)
            REFERENCES ".TABLE_PREFIX."threads(tid)
    );");

    // Create consensus_choices table
    $db->write_query("CREATE TABLE ".TABLE_PREFIX."consensus_choices (
        choice_id serial,
        poll_id serial,
        choice varchar(255) NOT NULL,
        PRIMARY KEY(choice_id),
        CONSTRAINT fk_poll_id
            FOREIGN KEY(poll_id)
            REFERENCES ".TABLE_PREFIX."consensus_polls(poll_id)
    );");

    // Create consensus votes table
    $db->write_query("CREATE TABLE ".TABLE_PREFIX."consensus_votes (
        vote_id serial,
        choice_id serial,
        vote_by_user_id integer,
        consensus_points smallint,
        PRIMARY KEY(vote_id),
        CONSTRAINT fk_choice
            FOREIGN KEY(choice_id)
            REFERENCES ".TABLE_PREFIX."consensus_choices(choice_id),
        CONSTRAINT fk_user_id
            FOREIGN KEY(vote_by_user_id)
            REFERENCES ".TABLE_PREFIX."users(uid)        
    );");
}

function consensus_is_installed()
{
    global $db;
    return $db->table_exists("consensus_polls") &&
            $db->table_exists("consensus_choices") &&
            $db->table_exists("consensus_status") &&
            $db->table_exists("consensus_votes");
}

function consensus_uninstall()
{
    global $db;
    $db->drop_table("consensus_votes");
    $db->drop_table("consensus_choices");
    $db->drop_table("consensus_polls");
    $db->drop_table("consensus_status");
}

function consensus_activate()
{
    insert_db_data();
    activate_templates();
}

function insert_db_data()
{
    global $db;

    $db->insert_query('consensus_status', array('status' => 'active'));
    $db->insert_query('consensus_status', array('status' => 'closed'));
    $db->insert_query('consensus_status', array('status' => 'inactive'));
    $db->insert_query('consensus_status', array('status' => 'expired'));
}

function activate_templates()
{
    require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

    find_replace_templatesets('showthread', '#'.preg_quote('{$addremovesubscription}').'#', "{\$addremovesubscription}\nConsensus");
    find_replace_templatesets('showthread', '#'.preg_quote('{$newreply}').'#', "Create Consensus{\$newreply}");
    find_replace_templatesets('showthread', '#'.preg_quote('{$pollbox}').'#', "{\$pollbox}\n{\$consensus_box}");

    global $lang, $db;
    $lang->load('consensus');

    $templatearray = array(
            'showthread' => '<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<thead>
		<tr>
			<td class="thead">
				<strong>{$lang->consensus}</strong>
			</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="tcat">
				<form method="POST" action="misc.php">
					<input type="hidden" name="consensus_post_key" value="{$mybb->post_code}" />
					<input type="hidden" name="action" value="create_consensus" />
					Hier w&uuml;rden die Abstimmungsm&ouml;glichkeiten stehen.
					<input type="text" name="message" class="textbox" /> <input type="submit" name="submit" class="button" value="{$lang->consensus_submit}" />
				</form>
			</td>
		</tr>
		<tr>
			<td class="trow1">
				Hier k&ouml;nnte Ihre Konsensierung stehen.
			</td>
		</tr>
	</tbody>
</table>
<br />',
        'post' => '<br /><br /><strong>{$lang->consensus_submit_done}</strong>'
    );

    $group = array(
        'prefix' => $db->escape_string('consensus'),
        'title' => $db->escape_string('MyBB Consensus')
    );

    // Update or create template group:
    $query = $db->simple_select('templategroups', 'prefix', "prefix='{$group['prefix']}'");

    if($db->fetch_field($query, 'prefix'))
    {
        $db->update_query('templategroups', $group, "prefix='{$group['prefix']}'");
    }
    else
    {
        $db->insert_query('templategroups', $group);
    }
}

function consensus_deactivate()
{
    deactivate_templates();
}

function deactivate_templates()
{
    require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

    find_replace_templatesets('showthread', '#'.preg_quote('Create Consensus').'#', '');
    find_replace_templatesets('showthread', '#'.preg_quote('Consensus').'#', '');
    find_replace_templatesets('showthread', '#'.preg_quote('\n{$consensus_box}').'#', '');
}

// Display the consensus poll in thread
function consensus_showthread()
{
    global $templates, $lang, $consensus_box;
    $lang->load('consensus');

    $consensus_box = eval($templates->render('consensus_showthread'));
}

// Create a new consensus poll
function consensus_new()
{
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

<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

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
        status serial,
        PRIMARY KEY (poll_id),
        CONSTRAINT fk_user_id
            FOREIGN KEY (created_by_user_id)
            REFERENCES ".TABLE_PREFIX."users(uid),
        CONSTRAINT fk_status
            FOREIGN KEY (status)
            REFERENCES ".TABLE_PREFIX."consensus_status(status_id)
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
// TODO
}

function consensus_deactivate()
{
// TODO
}
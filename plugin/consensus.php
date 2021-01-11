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
            "name"			=> "mybb-consensus",
            "description"	=> "A plugin for creating consensus polls",
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
// TODO
}

function consensus_is_installed()
{
    global $db;
    return $db->table_exists("mybb_consensus_polls");
}

function consensus_uninstall()
{
// TODO
}

function consensus_activate()
{
// TODO
}

function consensus_deactivate()
{
// TODO
}
<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}


function myplugin_info()
{
    return array(
            "name"			=> "mybb-consensus",
            "description"	=> "A plugin for creating consensus polls",
            "website"		=> "https://github.com/dieBasis-Partei/mybb-consensus",
            "author"		=> "dieBasis",
            "authorsite"	=> "https://www.diebasis-partei.de",
            "version"		=> "0.1-SNAPSHOT",
            "guid" 			=> "",
            "codename"		=> "mybb-consensus",
            "compatibility" => "*" // TODO
    );
}

function myplugin_install()
{
// TODO
}

function myplugin_is_installed()
{
// TODO
}

function myplugin_uninstall()
{
// TODO
}

function myplugin_activate()
{
// TODO
}

function myplugin_deactivate()
{
// TODO
}
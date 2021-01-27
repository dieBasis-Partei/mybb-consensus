<?php
    // Set some useful constants that the core may require or use
    define("IN_MYBB", 1);
    define('THIS_SCRIPT', 'new_consensus.php');

    // Including global.php gives us access to a bunch of MyBB functions and variables
    require_once "./global.php";

    require_once(MYBB_ROOT . 'inc/plugins/consensus/models/class_consensus.php');
    require_once(MYBB_ROOT . 'inc/plugins/consensus/models/class_proposal.php');

    // Only required because we're using misc_help for our page wrapper
    $lang->load("consensus");

    $action = $mybb->get_input('action');
    $post_code = $mybb->get_input('consensus_post_code');
    $date_format = 'Y-m-d\TH:i';

    if ($action == 'create') {
        verify_post_check($post_code);

        if ($mybb->request_method != 'post') {
            error_no_permission();
        }

        $proposals = $mybb->get_input('proposals');

        $sugs = array();
        for($i = 1; $i <= $proposals; $i++) {
            $sug_title = $mybb->get_input('consensus_proposal_title_'.$i);
            $sug_description = $mybb->get_input('consensus_proposal_description_'.$i);
            $sugs[] = new Proposal($sug_title, $sug_description, $i, 0);
        }

        global $db;
        $consensus_title = $mybb->get_input('consensus_title');
        $consensus_description = $mybb->get_input('consensus_description');
        $consensus_expires = DateTime::createFromFormat($date_format, $mybb->get_input('consensus_expires'));
        $thread_id = $mybb->get_input('tid');
        $user_id = $mybb->user['uid'];

        require_once(MYBB_ROOT . 'inc/plugins/consensus/models/class_status.php');
        require_once(MYBB_ROOT . 'inc/plugins/consensus/dao/class_status_dao.php');
        $statusDao = new StatusDao($db);
        $status = $statusDao->find_status_by_name(Status::STATUS_ACTIVE);

        $status_id = $status->getId();

        $consensus = new Consensus($consensus_title, $consensus_description, $consensus_expires, $user_id, $thread_id, $status_id, $sugs);
        require_once(MYBB_ROOT . 'inc/plugins/consensus/dao/class_consensus_dao.php');

        $dao = new ConsensusDao($db);
        if ($dao->insert($consensus) === true) {
            redirect("showthread.php?tid={$thread_id}", $lang->consensus_created);
        } else {
            error($lang->consensus_error_create);
        }
    }
    else
    {
        $plugins->run_hooks("consensus_create_start");


        $thread = get_thread($mybb->input['tid']);
        if (!$thread || ($thread['visible'] != 1 && ($thread['visible'] == 0 && !is_moderator($thread['fid'], "canviewunapprove")) || ($thread['visible'] == -1 && !is_moderator($thread['fid'], "canviewdeleted")))) {
            error($lang->error_invalidthread);
        }
        // TODO: Check permissions.


        /**
         * @param $thread
         * @param MyLanguage $lang
         */
        function render_breadcrumb($thread, $lang)
        {
            build_forum_breadcrumb($thread['fid']);
            add_breadcrumb(htmlspecialchars_uni($thread['subject']), get_thread_link($thread['tid']));
            add_breadcrumb($lang->consensus_add);
        }

        render_breadcrumb($thread, $lang);


        $plugins->run_hooks("consensus_create_end");

        $proposals = $mybb->input['proposals'] ? $mybb->input['proposals'] : 1;

        $consensus_point_index = 1;
        for (; $consensus_point_index < $proposals + 1; $consensus_point_index++) {
            eval("\$consensus_create_form_proposals .= \"" . $templates->get('consensus_create_form_proposal') . '";');
        }

        $date = new DateTime();
        $date->add(new DateInterval('P14D'));
        $consensus_default_expiry = $date->format($date_format);
        $tid = $thread['tid'];
        eval('$sections  = "' . $templates->get('consensus_create_form') . '";');

        // Using the misc_help template for the page wrapper
        eval('$page = "' . $templates->get("misc_help") . '";');

        // Spit out the page to the user once we've put all the templates and vars together
        output_page($page);
    }


?>


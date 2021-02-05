<?php

class ConsensusTemplates
{

    private $db;
    private $group;

    public function __construct(DB_Base $db) {
        $this->db = $db;
        $this->group = array(
                'prefix' => $this->db->escape_string('consensus'),
                'title' => $this->db->escape_string('MyBB Consensus')
        );
    }

    public function install() {
        $this->install_template_group();
        $this->install_templates();
    }

    private function install_templates() {
        $templates_array = array(
                'display_form_consensus' =>
                        '<form method="POST" action="misc.php">
                            <input type="hidden" name="action" value="consensus_vote" />
                            <input type="hidden" name="consensus_post_code" value="{$mybb->post_code}" />
                            <input type="hidden" name="thread_id" value="{$consensus->getThreadId()}" />
                            <input type="hidden" name="consensus_id" value="{$consensus->getConsensusId()}">
                            <input type="hidden" name="consensus_proposals_size" value="{$number_of_proposals}">
                            <table border="0" cellspacing="0" cellpadding="5" class="tborder">
		                        <thead>
			                        <tr>
				                        <td class="thead" colspan="2">
					                        <strong>{$lang->consensus}: {$consensus->getTitle()} {$expiry}</strong>
				                        </td>
			                        </tr>
			                    </thead>
			                    <tbody>
                                    <tr>
				                        <td class="trow1" style="text-align: left; vertical-align: top;" colspan="2">
				                            {$consensus->getDescription()}
				                        </td>
                                    </tr>
                                    <tr>
                                        <td class="trow1" style="text-align: left; vertical-align: top;" colspan="2">
                                            <img src="images/icons/information.png" alt="{$lang->consensus_form_tutorial_info}" /> <strong>{$lang->consensus_form_tutorial_title}</strong><br />
                                            {$lang->consensus_form_tutorial}
                                        </td>
                                    </tr>
			                        {$proposals}
                                    <tr>
				                        <td class="trow1" style="text-align: left; vertical-align: top;">
				                            <input type="submit" name="submit" class="button" value="{$lang->consensus_submit}" {$read_mode} /> {$notice_already_voted}
				                        </td>
				                        <td class="trow1">
				                            {$lang->consensus_resistance_points_scala}
                                        </td>
                                    </tr>
			                    </tbody>
			                </table>
			            </form>{$close_consensus}',
                'close_form_consensus' => '<form method="post" action="misc.php">
                            <input type="hidden" name="action" value="consensus_close" />
                            <input type="hidden" name="consensus_post_code" value="{$mybb->post_code}" />
                            <input type="hidden" name="consensus_id" value="{$consensus->getConsensusId()}">
                            <input type="hidden" name="thread_id" value="{$consensus->getThreadId()}" />
                            <input type="submit" class="button" value="{$lang->consensus_close_caption}">
                        </form>',
                'display_form_proposal' =>
                        '<tr>
				            <td class="trow1" style="text-align: left; vertical-align: top;" colspan="2">
					            <strong>{$lang->consensus_question} {$proposal->getPosition()} - {$proposal->getTitle()}</strong>
				            </td>
				        </tr>
				        <tr>
				            <td class="trow1" style="text-align: justify;" colspan="2">
					            {$proposal->getDescription()}
					            <input type="hidden" name="proposal_{$proposal->getPosition()}" value="{$proposal->getId()}">
				            </td>
			            </tr>
			            <tr>
				            <td class="trow1" style="text-align: left;" colspan="2">
					            <strong style="margin-right: 2em;">{$lang->consensus_proposal_caption_points}</strong>
                                {$points_metric}
				            </td>
			            </tr>',
                'display_form_proposal_points' =>
                '<input {$checked} {$disabled} type="radio" class="radio" name="proposal_points_{$proposal->getPosition()}" id="{$proposal->getPosition()}_{$resistance_points}" value="{$resistance_points}" /><label for="{$proposal->getPosition()}_{$resistance_points}">{$resistance_points_label}</label>',
                'create_form' => '
                    <script language="JavaScript">
                        function addPoints() {
                            let number_of_proposals = document.getElementById("number_points").value;
                            
                            if (number_of_proposals < 1) {
                                number_of_proposals = 1;
                            }
                            let url = new URL(document.URL);
                            url.searchParams.set(\'proposals\', number_of_proposals);
                            document.location.href =  url.toString();
                        }
                    </script>
                    <form method="POST" action="new_consensus.php">
                        <input type="hidden" name="consensus_post_code" value="{$mybb->post_code}" />
					    <input type="hidden" name="action" value="create" />
					    <input type="hidden" name="proposals" value="{$proposals}">
					    <input type="hidden" name="tid" value="{$tid}">
                        <table border="0" cellspacing="0" cellpadding="5" class="tborder">
                            <thead>
                                <tr>
                                    <td class="thead" colspan="2">
                                        <strong>{$lang->consensus_add}</strong>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="trow1" style="text-align: justify; padding-right: 1em;" colspan="2">
                                        <label for="number_points">{$lang->consensus_proposal_caption_add}:</label><br />
                                        <input type="number" id="number_points" value="{$proposals}" min="1" max="10" />
                                        <input type="button" onclick="addPoints()" value="{$lang->consensus_proposal_add}" /> {$lang->consensus_proposal_add_notice}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="trow1" style="text-align: left; vertical-align: top; padding-right: 1em;" colspan="2">
                                        <label for="consensus_title">{$lang->consensus_title}:</label><br />
                                        <input id="consensus_title" name="consensus_title" type="text" maxlength="255" style="width: 100%;" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="trow1" style="text-align: justify; padding-right: 1em;" colspan="2">
                                        <label for="consensus_description">{$lang->consensus_description}:</label><br />
                                        <textarea style="width: 100%; height: 5em;"  id="consensus_description" name="consensus_description"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="trow1" style="text-align: left;" colspan="2" style="padding-right: 1em;">
                                        {$consensus_create_form_proposals}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="trow1" style="text-align; left;">
                                        {$lang->consensus_expires}:<br />
                                        <input type="datetime-local" name="consensus_expires" class="button" value="{$consensus_default_expiry}" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="trow1" style="text-align; left;">
                                        <input type="submit" name="submit" class="button" value="{$lang->consensus_start}" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                ',
                'create_form_proposal' => '
                    <table id="consensus_point_{$consensus_point_index}" border="0" cellpadding="5" cellspacing="0" class="tborder" style="width: 100%;">
                        <tr>
                            <td class="trow_selected" style="text-align: left; vertical-align: top; padding-right: 1em;" colspan="2">
                                <label for="consensus_proposal_title_{$consensus_point_index}"><strong>{$lang->consensus_proposal_title} {$consensus_point_index}:</strong></label><br />
                                <input name="consensus_proposal_title_{$consensus_point_index}" id="consensus_proposal_title_{$consensus_point_index}" type="text" maxlength="255" style="width: 100%;" />
                            </td>
                        </tr>
                        <tr>
                            <td class="trow_selected" style="text-align: justify; padding-right: 1em;" colspan="2">
                                <label for="consensus_proposal_description_{$consensus_point_index}">{$lang->consensus_proposal_description}:</label><br />
                                <textarea style="width: 100%; height: 5em;"  id="consensus_proposal_description_{$consensus_point_index}" name="consensus_proposal_description_{$consensus_point_index}""></textarea>
                            </td>
                        </tr>
                    </table>',
                'display_consensus' => '
                    <table border="0" cellspacing="0" cellpadding="5" class="tborder" style="width: 100%;">
                        <thead>
                            <tr>
                                <td class="thead" colspan="2">
                                    <strong>{$lang->consensus_proposal_results_caption_title}: {$consensus->getTitle()} {$consensus_closed}</strong>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="trow1" style="text-align: left; vertical-align: top;" colspan="2">
                                    {$consensus->getDescription()}
                                </td>
                            </tr>
                            {$results}
                        </tbody>
                    </table>',
                'display_results' => '
                        <tr>
				            <td class="trow1" style="text-align: left; vertical-align: top;" colspan="2">
					            <strong>{$lang->consensus_question} {$proposal->getPosition()} - {$proposal->getTitle()}</strong>
				            </td>
				        </tr>
				        <tr>
				            <td class="trow1" style="text-align: justify;" colspan="2">
					            {$proposal->getDescription()}
				            </td>
			            </tr>
			            <tr>
				            <td class="trow1" style="text-align: left;" colspan="2">
					            <strong style="margin-right: 2em;">{$lang->consensus_results_proposal_results} {$proposal->getPosition()}</strong>
					            <table style="text-align: left; width: 100%;">
					                {$proposal_results}
                                </table>
				            </td>
			            </tr>
			            <tr>
			                <td colspan="2"><hr /></td>
                        </tr>',
                'display_results_proposal_results' => '
						<tr>
							<td>
								<div style="width: 50em; height: 1em; display: inline-block; background-image: linear-gradient(to right, darkred, yellow, darkgreen);">
									<div style="width: {$proposal_progress_size}em; height: 1em; background-color: white; opacity: 80%; float: right;">&nbsp;</div>
								</div>
							</td>
						</tr>
						<tr>
							<td style="text-align: left;">
                                {$proposal_summary_results}
							</td>
						</tr>',
                'display_results_summary' => '
                    <table>
                    <tr>
                        <td><strong>{$lang->consensus_proposal_results_caption_total}</strong></td>
                    </tr>
                    <tr>
                        <td>{$summary_total_points} $lang->consensus_proposal_results_caption_points ({$summary_total_votes} {$lang->consensus_proposal_results_caption_votes}, {$summary_no_votes} {$lang->consensus_proposal_results_caption_no_opinion})</td>
                    </tr>
                    <tr>
                        <td><strong>{$lang->consensus_proposal_results_caption_conclusion}: {$summary_total_acceptance_percent_rounded}% {$lang->consensus_proposal_results_caption_approval}</strong></td>
                    </tr>
                    <tr>
                        <td>{$lang->consensus_proposal_results_caption_formula}: 100 - (({$summary_total_points} / {$summary_total_votes}) * 10) =  {$summary_total_acceptance_percent}%</td>
                    </tr>
                    </table>'
        );

        $prefix = $this->group['prefix'];
        // Query already existing templates.
        $query = $this->db->simple_select('templates', 'tid,title,template', "sid=-2 AND (title='{$prefix}' OR title LIKE '{$prefix}=_%' ESCAPE '=')");

        $templates = $duplicates = array();

        while($row = $this->db->fetch_array($query)) {
            $title = $row['title'];
            $row['tid'] = (int)$row['tid'];

            if (isset($templates[$title])) {
                // PluginLibrary had a bug that caused duplicated templates.
                $duplicates[] = $row['tid'];
                $templates[$title]['template'] = false; // force update later
            } else {
                $templates[$title] = $row;
            }
        }

        // Delete duplicated master templates, if they exist.
        if ($duplicates) {
            $this->db->delete_query('templates', 'tid IN ('.implode(",", $duplicates).')');
        }

        // Update or create templates.
        foreach($templates_array as $name => $code) {
            if(strlen($name)) {
                $name = "{$prefix}_{$name}";
            } else {
                $name = "{$prefix}";
            }

            $template = array(
                    'title' => $this->db->escape_string($name),
                    'template' => $this->db->escape_string($code),
                    'version' => 1,
                    'sid' => -2,
                    'dateline' => TIME_NOW
            );

            // Update
            if (isset($templates[$name])) {
                if($templates[$name]['template'] !== $code) {
                    // Update version for custom templates if present
                    $this->db->update_query('templates', array('version' => 0), "title='{$template['title']}'");

                    // Update master template
                    $this->db->update_query('templates', $template, "tid={$templates[$name]['tid']}");
                }
            } else { // Create
                $this->db->insert_query('templates', $template);
            }

            // Remove this template from the earlier queried list.
            unset($templates[$name]);
        }

        // Remove no longer used templates.
        foreach($templates as $name => $row) {
            $this->db->delete_query('templates', "title='{$this->db->escape_string($name)}'");
        }
    }

    public function activate_templates() {
        require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
        find_replace_templatesets('showthread', '#'.preg_quote('{$newreply}').'#', "{\$newconsensus}{\$newreply}");
        find_replace_templatesets('showthread', '#'.preg_quote('{$pollbox}').'#', "{\$pollbox}\n\t{\$consensusbox}");
    }

    private function install_template_group() {
        $prefix = $this->group['prefix'];
        $query = $this->db->simple_select('templategroups', 'prefix', "prefix='{$prefix}'");

        if ($this->db->fetch_field($query, 'prefix')) {
            $this->db->update_query('templategroups', $this->group, "prefix='{$prefix}'");
        } else {
            $this->db->insert_query('templategroups', $this->group);
        }
    }

    public function deactivate_templates() {
        require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

        find_replace_templatesets('showthread', '#'.preg_quote('{$newconsensus}').'#', '');
        find_replace_templatesets('showthread', '#'.preg_quote("\n\t{\$consensusbox}").'#', '');
    }

    public function uninstall() {
        $this->db->delete_query('templategroups', "prefix='{$this->group['prefix']}'");
        $this->db->delete_query('templates', "title='consensus' OR title LIKE 'consensus_%'");
    }

    public function renderForm() {

    }
}

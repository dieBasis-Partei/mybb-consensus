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
                            <table border="0" cellspacing="0" cellpadding="5" class="tborder">
		                        <thead>
			                        <tr>
				                        <td class="thead" colspan="2">
					                        <strong>{$lang->consensus}: {$consensus->getTitle()}</strong>
				                        </td>
			                        </tr>
			                    </thead>
			                    <tbody>
                                    <tr>
				                        <td class="trow1" style="text-align: left; vertical-align: top;" colspan="2">
				                            {$consensus->getDescription()}
				                        </td>
                                    </tr>
			                        {$proposals}
                                    <tr>
				                        <td class="trow1" style="text-align: left; vertical-align: top;" colspan="2">
				                            <input type="submit" name="submit" class="button" value="{$lang->consensus_submit}" />
				                        </td>
                                    </tr>
			                    </tbody>
			                </table>
			                {$lang->consensus_resistance_points_scala}
			            </form>',
                'display_form_proposal' =>
                        '
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
					            <strong style="margin-right: 2em;">{$lang->consensus_proposal_caption_points}</strong>
                                {$points_metric}
				            </td>
			            </tr>',
                'display_form_proposal_points' =>
                '<input type="radio" class="radio" name="{$proposal->getId()}" id="{$proposal->getId()}_{$resistance_points}" value="{$resistance_points}" /><label for="{$proposal->getId()}_{$resistance_points}">{$resistance_points_label}</label>',
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
                                    <td class="trow1" style="text-align: justify; padding-right: 1em;" colspan="2">
                                        <label for="number_points">{$lang->consensus_proposal_caption_add}:</label><br />
                                        <input type="number" id="number_points" value="{$proposals}" min="1" max="10" />
                                        <input type="button" onclick="addPoints()" value="{$lang->consensus_proposal_add}" />
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
                'post' => '<br /><br /><strong>{$lang->consensus_submit_done}</strong>'
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

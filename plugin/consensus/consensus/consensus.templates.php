<?php

class ConsensusTemplateSetup {

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
                'showthread' =>
                        '<form method="POST" action="misc.php">
                            <table border="0" cellspacing="0" cellpadding="5" class="tborder">
		                        <thead>
			                        <tr>
				                        <td class="thead" colspan="2">
					                        <strong>{$lang->consensus}: {$consensus_title}</strong>
				                        </td>
			                        </tr>
			                    </thead>
			                    <tbody>
			                        {$questions}
			                    </tbody>
			                </table>
			            </form>',
                'showthread_questions' =>
                        '
                        <tr>
				            <td class="trow1" style="text-align: left; vertical-align: top;">
					            <strong>{$lang->consensus_question} {$consensus_question_number} - ${consensus_question_title}</strong>
				            </td>
				            <td class="trow1" style="text-align: justify;">
					            {$consensus_question_description}
				            </td>
			            </tr>
			            <tr>
				            <td class="trow1" style="text-align: left;" colspan="2">
					            <strong style="margin-right: 2em;">Widerstandspunkte</strong>
                                <input type="radio" class="radio" name="{$question_id}" id="option_0" value="0" /><label for="option_0">0</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_1" value="1" /><label for="option_1">1</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_2" value="2" /><label for="option_2">2</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_3" value="3" /><label for="option_3">3</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_4" value="4" /><label for="option_4">4</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_5" value="5" /><label for="option_5">5</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_6" value="6" /><label for="option_6">6</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_7" value="7" /><label for="option_7">7</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_8" value="8" /><label for="option_8">8</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_9" value="9" /><label for="option_9">9</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_10" value="10" /><label for="option_10">10</label>
                                <input type="radio" class="radio" name="{$question_id}" id="option_11" value="-1" /><label for="option_11">Keine eigene Meinung</label>
				            </td>
			            </tr>',
                ''
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
        require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
        find_replace_templatesets('showthread', '#'.preg_quote('{$newreply}').'#', "{\$newconsensus}{\$newreply}");
        find_replace_templatesets('showthread', '#'.preg_quote('{$pollbox}').'#', "{\$pollbox}\n{\$consensusbox}");
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
        require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

        find_replace_templatesets('showthread', '#'.preg_quote('{$newconsensus}').'#', '');
        find_replace_templatesets('showthread', '#'.preg_quote('\n{$consensusbox}').'#', '');
    }

    public function uninstall() {
        $this->db->delete_query('templategroups', "prefix='{$this->group['prefix']}'");
        $this->db->delete_query('templates', "title='consenus' OR title LIKE 'consensus_%'");
    }

}

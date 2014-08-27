<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


require_once($CFG->libdir . '/formslib.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');

/**
 * The literature list view form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_list_view_form extends moodleform {



    public function definition() {

		global $CFG; 
 
        $mform = $this->_form;

        // Listinfo
        $mform->addElement('header', 'lit_list_header', get_string('listinfo', 'literature'));

        // Name
        $mform->addElement('text', 'name', get_string('name'), array('size' => '40'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        if (!empty($this->_customdata->name)) {
            $mform->setDefault('name', $this->_customdata->name);
        }

        // Description
        $mform->addElement('textarea', 'desc', get_string('description', 'literature'), array('rows' => 5, 'cols' => 90));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('desc', PARAM_TEXT);
        } else {
            $mform->setType('desc', PARAM_CLEANHTML);
        }
        if (!empty($this->_customdata->desc)) {
            $mform->setDefault('desc', $this->_customdata->desc);
        }

        // Is public?
        $mform->addElement('advcheckbox', 'public', get_string('ispublic', 'literature'), null, null, array(0, 1));
        $mform->addHelpButton('public', 'help:addlist:public', 'literature');
        if (!empty($this->_customdata->public)) {
            $mform->setDefault('public', $this->_customdata->public);
        }


        // Save
        $mform->addElement('submit', 'btn_save', get_string('save', 'literature'));


		// elements for SA
		if ($CFG->literature_sa_enabled) {
		

			// Semesterapparat
			$mform->addElement('header', 'lit_sa_header', get_string('sa_header', 'literature'));
		
		
	        // is SA?
			$mform->addElement('advcheckbox', 'sa', get_string('sa', 'literature'), null, null, array(0, 1));
			// $mform->addHelpButton('public', 'help:addlist:public', 'literature');
			if (!empty($this->_customdata->sa)) {
            $mform->setDefault('sa', $this->_customdata->sa);
			}


			// Location of SA
            $items = array();
            $items['sel'] = get_string('sa_selectloc', 'literature');
            
            // read the possible locations for SA from config
            $locs = explode( ";", $CFG->literature_sa_librarylocations);
            // add to array with key = value
			foreach ($locs as $loc)
				$items[$loc] = $loc;

            $mform->addElement('select', 'sa_location', get_string('sa_location', 'literature'), $items);
            if (!empty($this->_customdata->sa_location)) {
				$mform->setDefault('sa_location', $this->_customdata->sa_location);
			}


			// Semester for SA
            $items = array();
            $items['sel'] = get_string('sa_selectsem', 'literature');
			
			// calculate possible semesters according to current year
			// start with WS 1 year back and end 5 years from now
			$today = getdate();
			for ($year = ((int) $today['year'] - 1); $year <= (int) $today['year'] + 5; $year++) {
				$str_ws = "WS " . ($year - 1) . "/" . $year;
				$str_ss = "SS " . $year;
				
				$items[$str_ws] = $str_ws;
				$items[$str_ss] = $str_ss;
			}
			
            $mform->addElement('select', 'sa_semester', get_string('sa_semester', 'literature'), $items);			
            if (!empty($this->_customdata->sa_semester)) {
				$mform->setDefault('sa_semester', $this->_customdata->sa_semester);
			}


			// Course for SA
			global $DB, $USER;
			
 			// roleid 1 = manager, 2 = coursecreator, 3 = editingteacher, (5 = student)
			// get all courses where user is 1, 2 or 3
			$records = $DB->get_records_sql('SELECT c.shortname                     
			FROM mdl_course c 
			JOIN mdl_context ct ON c.id = ct.instanceid
			JOIN mdl_role_assignments ra ON ra.contextid = ct.id
			JOIN mdl_user u ON u.id = ra.userid
			JOIN mdl_role r ON r.id = ra.roleid 
			WHERE (ra.roleid = 1 OR ra.roleid = 2 OR ra.roleid = 3) AND u.id = :userid ', array('userid' => $USER->id));

            $items = array();
            $items['sel'] = get_string('sa_selectcourse', 'literature');

			foreach ($records as $key => $value)
				$items[$key] = $key;

		
            $mform->addElement('select', 'sa_course', get_string('sa_course', 'literature'), $items);			
            if (!empty($this->_customdata->sa_course)) {
				$mform->setDefault('sa_course', $this->_customdata->sa_course);
			}


			
			// SA code
			if (!empty($this->_customdata->sa_code)) {
				$sentmessage = $this->_customdata->sa_code;
				$mform->addElement('static', 'sa_code', get_string('sa_code', 'literature'), $sentmessage);
			}


			// URL, clickable
			$sa_url = $CFG->literature_sa_libraryurlprefix . substr(str_replace(' ', '', $USER->lastname), 0, 3) . substr(str_replace(' ', '', $this->_customdata->sa_course), 0, 3) . str_replace(' ', '', str_replace('/', '', $this->_customdata->sa_semester));
			
			$sa_url_clickable = '<a href="' . $sa_url . '">' . $sa_url . '</a>';
			
			$mform->addElement('static', 'sa_code', get_string('sa_url', 'literature'), $sa_url_clickable);
			


			// Comment for SA to Library
			$mform->addElement('textarea', 'sa_comment', get_string('sa_comment', 'literature'), array('rows' => 3, 'cols' => 90));
			if (!empty($CFG->formatstringstriptags)) {
				$mform->setType('sa_comment', PARAM_TEXT);
			} else {
				$mform->setType('sa_comment', PARAM_CLEANHTML);
			}
           if (!empty($this->_customdata->sa_comment)) {
				$mform->setDefault('sa_comment', $this->_customdata->sa_comment);
			}


			// group buttons, so they appear besides each other
			$actionarray = array();
			// Save
            $actionarray[] = &$mform->createElement('submit', 'btn_save', get_string('save', 'literature'));
            // Save & Send to library
            $actionarray[] = &$mform->createElement('submit', 'btn_saveandsend', get_string('sa_saveandsend', 'literature'));

            $mform->addGroup($actionarray);

			// SA already sent? and when? 
			if ($this->_customdata->sa_sent) {
				$sent_year = substr($this->_customdata->sa_sentdate, 0, 4);
				$sent_month = substr($this->_customdata->sa_sentdate, 4, 2);
				$sent_day = substr($this->_customdata->sa_sentdate, 6, 2);
				$sentmessage = get_string('sa_sent1', 'literature') . $sent_day . "." . $sent_month . "." . $sent_year . get_string('sa_sent2', 'literature');
				$mform->addElement('static', 'sa_sent', get_string('sa_sent0', 'literature'), $sentmessage);
			}

        }
	

 




        // Items
        $mform->addElement('header', 'lit_items_header', get_string('lit', 'literature'));
        $list = literature_print_literaturelist($this->_customdata->content);
        $mform->addElement('html', $list);

        // Actions
        if ($this->_customdata->incourse) {

            $mform->addElement('header', 'lit_list_header', get_string('settings', 'literature'));
            $opt_list = get_string('view_as_list', 'literature');
            $opt_full = get_string('view_as_full', 'literature');
            $fields = array('0' => $opt_list, '1' => $opt_full);
            $mform->addElement('select', 'view', get_string('postas', 'literature'), $fields);
            $mform->closeHeaderBefore('btn_post');

            $mform->addElement('submit', 'btn_post', get_string('post', 'literature'));
        } else {

            $mform->addElement('header', 'act_header', get_string('actions', 'literature'));

            $items = array();
            $items['sel'] = get_string('actionsel', 'literature');
            $items['del'] = get_string('delete', 'literature');
            $items['imp'] = get_string('importlit', 'literature');
            $items['exp'] = get_string('export', 'literature');
            $items['add'] = get_string('addlit', 'literature');

            $actionarray = array();
            $actionarray[] = &$mform->createElement('select', 'act_select', null, $items);
            $actionarray[] = &$mform->createElement('submit', 'btn_go', get_string('ok'));

            $mform->addGroup($actionarray);
        }
    }


}


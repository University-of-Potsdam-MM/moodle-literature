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


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/locallib.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * The configuration form for searchsources of type "opacxml"
 * *
 * @package    mod_literature_searchsource
 * @subpackage opacxml
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class literature_searchsource_opacxml_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'source', get_string('pluginname', 'searchsource_opacxml'));

        // Name
        $mform->addElement('text', 'name', get_string('name', 'searchsource_opacxml'), array('size' => 50));
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        // Server
        $mform->addElement('text', 'server', get_string('server', 'searchsource_opacxml'), array('size' => 50));
        $mform->addRule('server', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('server', 'help:server', 'searchsource_opacxml');

        $mform->closeHeaderBefore('submitgroup');

        $submitgroup = array();
        $submitgroup[] = &$mform->createElement('submit', 'save', get_string('save', 'searchsource_opacxml'));
        $submitgroup[] = &$mform->createElement('cancel');

        $mform->addGroup($submitgroup, 'submitgroup');
    }

}
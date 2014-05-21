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

/**
 * This file keeps track of upgrades to the literature module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod
 * @subpackage literature
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Execute literature upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_literature_upgrade($oldversion) {
    global $DB;
    global $CFG;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

	$result = TRUE;

    // Insert update code here
    
    if ($oldversion < 2014050500) {



       // Define field sa to be added to literature_lists.
        $table = new xmldb_table('literature_lists');
        $field = new xmldb_field('sa', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'public');

        // Conditionally launch add field sa.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field sa_location to be added to literature_lists.
        $table = new xmldb_table('literature_lists');
        $field = new xmldb_field('sa_location', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'sa');

        // Conditionally launch add field sa_location.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        
        // Define field sa_code to be added to literature_lists.
        $table = new xmldb_table('literature_lists');
        $field = new xmldb_field('sa_code', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'sa_location');

        // Conditionally launch add field sa_code.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }        


        // Define field sa_comment to be added to literature_lists.
        $table = new xmldb_table('literature_lists');
        $field = new xmldb_field('sa_comment', XMLDB_TYPE_TEXT, null, null, null, null, null, 'sa_code');

        // Conditionally launch add field sa_comment.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


       // Define field sa_sent to be added to literature_lists.
        $table = new xmldb_table('literature_lists');
        $field = new xmldb_field('sa_sent', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'sa_comment');

        // Conditionally launch add field sa_sent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


       // Define field sa_sent_date to be added to literature_lists.
        $table = new xmldb_table('literature_lists');
        $field = new xmldb_field('sa_sentdate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'sa_sent');

        // Conditionally launch add field sa_sent_date.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }



        


        // Literature savepoint reached.
        upgrade_mod_savepoint(true, 2014050500, 'literature');
    }
    
	if ($oldversion < 2014052000) {

        // Rename field sa_sent_date on table literature_lists to sa_sentdate.
        $table = new xmldb_table('literature_lists');
        $field = new xmldb_field('sa_sent_date', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'sa_sent');

        // Launch rename field sa_sentdate.
        $dbman->rename_field($table, $field, 'sa_sentdate');

        // Literature savepoint reached.
        upgrade_mod_savepoint(true, 2014052000, 'literature');
    }
    
    
    return $result;
}

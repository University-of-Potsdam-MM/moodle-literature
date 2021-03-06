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
 * Library for the searchsource "srugbv"
 *
 * Implements neccesary functions for subplugins of type "searchsource" and others
 *
 * @package    mod_literature_searchsource
 * @subpackage srugbv
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Process the form of searchsource view form
 * @param array $formdata The form data
 * @return array with the defaultvalues and a url for the form
 */
function literature_searchsource_srugbv_processform($formdata) {

    if (!empty($formdata['submitgroup']['save'])) {

        $source = new literature_searchsource_srugbv_dbobject();
        $source->name = trim($formdata['name']);
        $source->bibcode = trim($formdata['bibcode']);

        if ($formdata['id'] != -1) {
            $id = $formdata['id'];
            literature_searchsource_upddate($id, $source);
        } else {
            if (!$id = literature_searchsource_add('srugbv', $source)) {
                print_error('error:searchsource:addfailed', 'literature');
            }
        }

        $defaultvalues = new stdClass();
        $url = new moodle_url('/mod/literature/searchsource/index.php');
    } else {

        $defaultvalues = new stdClass();
        $url = new moodle_url('/mod/literature/searchsource/index.php');
    }

    return array($defaultvalues, $url);
}

/**
 * Build search connectors for the sru search form
 * @return array String array with the connectors
 */
function literature_searchsource_srugbv_build_searchconnectors() {

    $connectors = array();
    $connectors['and'] = get_string('and', 'literature');
    $connectors['or'] = get_string('or', 'literature');
    // $connectors['@not'] = get_string('andnot', 'literature');

    return $connectors;
}

/**
 * Make formdata for sarch form after quicksearch
 *
 * @param string $text The searchtems
 * @param int $sourceid The id of the source to search in
 * @param int $course The id of the course
 * @param int $section The id of the section
 * @return stdClass The data for the search form
 */
function literature_searchsource_srugbv_build_formdata($text, $sourceid, $course, $section) {

    if (!$sourceinfo = literature_searchsource_load_info($sourceid)) {
        print_error('error:searchsource:confignotfound', 'literature');
    }

    $formdata = new stdClass();
    $formdata->search_group0['field_type'] = 'all';
    $formdata->search_group0['search_field'] = $text;
    $formdata->type = 'srugbv';
    $formdata->set = 'pica';
    $formdata->source = $sourceid;
    $formdata->course = $course;
    $formdata->section = $section;

    return $formdata;
}
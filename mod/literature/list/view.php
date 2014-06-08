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
 * Script to display and process literature list view form
 *
 * @package    mod_literature_list
 * @copyright  2012 Frederik Strelczuk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/course/lib.php');
require_once('view_form.php');
require_once(dirname(dirname(__FILE__)) . '/dbobject/literaturelist.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');


$id = required_param('id', PARAM_INT);
$courseid = optional_param('course', -1, PARAM_INT);
$section = optional_param('section', -1, PARAM_INT);

if (!$list = literature_dbobject_literaturelist::load_by_id($id)) {
    $a = new stdClass();
    $a->listid = $id;
    print_error('error:list:loadfailed', 'literature', null, $a);
}

// Check if access should be denied
if ($list->info->userid != $USER->id && !$list->info->public) {
    print_error('error:list:accessdenied', 'literature');
}


if ($courseid != -1 && $section != -1) {

    ////////////////////////////////////////////////////////////////////////////////
    // In COURSE context
    ////////////////////////////////////////////////////////////////////////////////

    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('mod/literature:addinstance', $context);

    $url = new moodle_url('/mod/literature/list/view.php');
    $url->param('course', $courseid);
    $url->param('section', $section);
    $url->param('id', $id);

    $PAGE->set_url($url);
    $PAGE->set_context($context);



    // Process
    if (!empty($_POST)) {

        if (!empty($_POST['btn_post'])) {

            $litids = (!empty($_POST['select'])) ? $_POST['select'] : null;
            $view = (isset($_POST['view'])) ? $_POST['view'] : 1;

            if (!empty($litids)) {

                $SESSION->literature_post_ids = array();
                foreach ($litids as $litid => $isselected) {
                    if ($isselected) {
                        $SESSION->literature_post_ids[] = $litid;
                    }
                }

                $url = new moodle_url('/mod/literature/lit/post.php');
                $url->param('course', $courseid);
                $url->param('section', $section);
                $url->param('view', $view);

                redirect($url);
            } else {
                $message = get_string('notify:nolitselected', 'literature');
            }
        }
    }


} else {

    ////////////////////////////////////////////////////////////////////////////////
    // In GLOBAL context
    ////////////////////////////////////////////////////////////////////////////////

    require_login();
    $context = context_user::instance($USER->id);
    require_capability('mod/literature:manage', $context);

    $url = new moodle_url('/mod/literature/list/view.php');
    $url->param('id', $id);
    $PAGE->set_url($url);
    $PAGE->set_context($context);

    // Process
    if (!empty($_POST)) {

        if (!empty($_POST['btn_save']) || !empty($_POST['btn_saveandsend'])) {

            $listname = $_POST['name'];
            $listdesc = (empty($_POST['desc'])) ? null : $_POST['desc'];
            $public = $_POST['public'];
            $sa = $_POST['sa'];
            $sa_location = (empty($_POST['sa_location'])) ? null : $_POST['sa_location'];

            // generate sa_code from location, user-id and course short name(?)
            // course id is not available for a list?
			// user can override sa_code with manual entry
			if (empty($_POST['sa_code'])) {
				// get current date
				$today = getdate();
				// put a 0 in front of month or day if less than 10
			    $today_mon = ($today['mon'] > 9) ? $today['mon'] : "0" . $today['mon'];
			    $today_mday = ($today['mday'] > 9) ? $today['mday'] : "0" . $today['mday'];
				$today_full = $today['year'] . $today_mon . $today_mday;

				$sa_code = $USER->firstname . "_" . $USER->lastname . "_" . $sa_location . "_" . $today_full;
			} else {
				$sa_code = $_POST['sa_code'];
			}

			$sa_comment = (empty($_POST['sa_comment'])) ? null : $_POST['sa_comment'];

            if (!$listinfo = literature_dbobject_listinfo::load_by_id($id)) {
                $listid = $id;
                print_error('error:list:loadfailed', 'literature', $PAGE->url, $listid);
            }
            $listinfo->name = $listname;
            $listinfo->description = $listdesc;
            $listinfo->public = $public;
            $listinfo->sa = $sa;
            $listinfo->sa_location = $sa_location;
            $listinfo->sa_code = $sa_code;
            $listinfo->sa_comment = $sa_comment;

            $listinfo->save();
            
            
            // send SA to library by e-mail
            if (!empty($_POST['btn_saveandsend'])) { 
            
			
			    global $CFG;
		    
			    // get e-mail address of logged-in user
			    $from = $USER->email;
			    
			    if (empty($USER->email)) {
			        debugging('Can not send email to user without email: '.$USER->id, DEBUG_DEVELOPER);
			    }			    
			    if (!validate_email($USER->email)) {
			        // We can not send emails to invalid addresses - it might create security issue or confuse the mailer.
			        $invalidemail = "User $USER->id (".fullname($USER).") email ($USER->email) is invalid! Not sending.";
			        error_log($invalidemail);
			        if (CLI_SCRIPT) {
			            mtrace('Error: lib/moodlelib.php email_to_user(): '.$invalidemail);
			        }
			    }
			    
	    
			    // construct subject from Username, Course(?) and Date
			    $today = getdate();
			    $subject = get_string('sa_emaillib_subject', 'literature') . " " . $USER->firstname . " " . $USER->lastname . ", " . $today['mday'] . "." . $today['mon'] . "." . $today['year'];
			

				// create text to send to library
//$messagetext = "Dies ist ein unlustiger Testtext.";

				$messagetext = literature_print_literaturelist(literature_dbobject_literaturelist::load_by_id($id)->items, 0);

//echo $messagetext;
			
		
				// prepare mail
			    $mail = get_mailer();
			
			    if (!empty($mail->SMTPDebug)) {
			        echo '<pre>' . "\n";
			    }
			
			    $temprecipients = array();
			    $tempreplyto = array();
			
			    $supportuser = core_user::get_support_user();
			
			    // Make up an email address for handling bounces.
			    if (!empty($CFG->handlebounces)) {
			        $modargs = 'B'.base64_encode(pack('V', $user->id)).substr(md5($user->email), 0, 16);
			        $mail->Sender = generate_email_processing_address(0, $modargs);
			    } else {
			        $mail->Sender = $supportuser->email;
			    }
			
			    if (is_string($from)) { // So we can pass whatever we want if there is need.
			        $mail->From     = $CFG->noreplyaddress;
			        $mail->FromName = $from;
			    } else if ($usetrueaddress and $from->maildisplay) {
			        $mail->From     = $from->email;
			        $mail->FromName = fullname($from);
			    } else {
			        $mail->From     = $CFG->noreplyaddress;
			        $mail->FromName = fullname($from);
			        if (empty($replyto)) {
			            $tempreplyto[] = array($CFG->noreplyaddress, get_string('noreplyname'));
			        }
			    }
			
			    if (!empty($replyto)) {
			        $tempreplyto[] = array($replyto, $replytoname);
			    }
			
			    $mail->Subject = substr($subject, 0, 900);
			
			    $temprecipients[] = array($CFG->literature_sa_email_library);
			
			    // Set word wrap.
			    $mail->WordWrap = 79;

		        $mail->IsHTML(false);
		        $mail->Body =  "\n$messagetext\n";
			
			
			    // Check if the email should be sent in an other charset then the default UTF-8.
			    if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {
			
			        // Use the defined site mail charset or eventually the one preferred by the recipient.
			        $charset = $CFG->sitemailcharset;
			        if (!empty($CFG->allowusermailcharset)) {
			            if ($useremailcharset = get_user_preferences('mailcharset', '0', $USER->id)) {
			                $charset = $useremailcharset;
			            }
			        }
			
			        // Convert all the necessary strings if the charset is supported.
			        $charsets = get_list_of_charsets();
			        unset($charsets['UTF-8']);
			        if (in_array($charset, $charsets)) {
			            $mail->CharSet  = $charset;
			            $mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
			            $mail->Subject  = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
			            $mail->Body     = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
			            $mail->AltBody  = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));
			
			            foreach ($temprecipients as $key => $values) {
			                $temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
			            }
			            foreach ($tempreplyto as $key => $values) {
			                $tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
			            }
			        }
			    }
			
			    foreach ($temprecipients as $values) {
			        $mail->addAddress($values[0]);
			    }
			
			    if ($mail->send()) {
			        set_send_count($USER);
			        if (!empty($mail->SMTPDebug)) {
			            echo '</pre>';
			        }
			        // save date and send status of SA to library
			        $listinfo->sa_sent = 1;
			        // put a 0 in front of month or day if less than 10
			        $today_mon = ($today['mon'] > 9) ? $today['mon'] : "0" . $today['mon'];
			        $today_mday = ($today['mday'] > 9) ? $today['mday'] : "0" . $today['mday'];
					$listinfo->sa_sentdate = $today['year'] . $today_mon . $today_mday;

					$listinfo->save();
 			        // hier sollte noch eine bestaetigende Ausgabe hin?
 			        
			       
			    } else {
			        add_to_log(SITEID, 'library', 'mailer', qualified_me(), 'ERROR: '. $mail->ErrorInfo);
			        if (CLI_SCRIPT) {
			            mtrace('Error: lib/moodlelib.php email_to_user(): '.$mail->ErrorInfo);
			        }
			        if (!empty($mail->SMTPDebug)) {
			            echo '</pre>';
			        }
			       
			    }
			
		
            
            
			}
            
        } else {

            $litids = (!empty($_POST['select'])) ? $_POST['select'] : null;

            switch ($_POST['act_select']) {

                case 'del' :

                    if ($litids != null) {

                        foreach ($litids as $litid => $isselected) {
                            if ($isselected) {
                                literature_dbobject_literaturelist::del_literature($id, $litid);
                            }
                        }
                    } else {
                        $message = get_string('notify:nolitselected', 'literature');
                    }
                    break;

                case 'exp' :

                    if ($litids != null) {
                 
                        $SESSION->literature_litselected = array();
                        foreach ($litids as $litid => $isselected) {
                            if ($isselected) {
                                $SESSION->literature_litselected[] = $litid;
                            }
                        }

                        $url = new moodle_url('/mod/literature/lit/export.php');
                        $url->param('listid', $id);
                        redirect($url);
                    } else {
                        $message = get_string('notify:nolitselected', 'literature');
                    }
                    break;

                case 'imp' :

                    $url = new moodle_url('/mod/literature/lit/import.php');
                    $url->param('listid', $id);
                    redirect($url);
                    break;

                case 'add' :
                    $url = new moodle_url('/mod/literature/lit/search.php');
                    $url->param('search', 'false');
                    $url->param('listid', $id); // TODO support in later versions
                    redirect($url);
                    break;

                default :
                    $message = get_string('notify:novalidaction', 'literature');
            }
        }
    }

    $node = $PAGE->navigation->find('literature_managelists', navigation_node::TYPE_CONTAINER);
    if ($node) {
        $listinfos = literature_dbobject_listinfo::load_by_userid($USER->id);
        foreach ($listinfos as $listinfo) {
            $url = new moodle_url($CFG->wwwroot . '/mod/literature/list/view.php');
            $url->param('id', $listinfo->id);

            $listnode = $node->add(
                    $listinfo->name, $url, navigation_node::TYPE_ACTIVITY
            );
            if ($listinfo->id == $id) {
                $listnode->make_active();
            }
        }
    }
}

$list = literature_dbobject_literaturelist::load_by_id($id);

$data = new stdClass();
$data->name = $list->info->name;
$data->desc = $list->info->description;
$data->public = $list->info->public;
$data->sa = $list->info->sa;
$data->sa_location = $list->info->sa_location;
$data->sa_code = $list->info->sa_code;
$data->sa_comment = $list->info->sa_comment;
$data->sa_sent = $list->info->sa_sent;
$data->sa_sentdate = $list->info->sa_sentdate;
$data->content = $list->items;
$data->listid = $list->info->id;
$data->incourse = ($section == -1 || $courseid == -1) ? false : true;


// Set page data
$title = get_string('view_list', 'literature');
$title .= ' ' . $list->info->name;
$PAGE->set_title($title);
$PAGE->set_heading($list->info->name);
$PAGE->set_pagelayout('standard');

// Output page
echo $OUTPUT->header();
if (!empty($message)) {
    echo $OUTPUT->notification($message);
}

$script = 'view.php?course=' . $courseid . '&section=' . $section . '&id=' . $id;
$mform = new literature_list_view_form($script, $data);
$mform->display();

// Finish the page
echo $OUTPUT->footer();


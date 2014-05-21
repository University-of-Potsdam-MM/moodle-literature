<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	require_once($CFG->dirroot.'/mod/literature/enricher/lib.php');

        $enrichers = literature_enricher_get_folders();
	foreach ($enrichers as $enrichername) {
		$name = 'literature_enricher_' . $enrichername;
		$dirname = $enrichername;

		if(!isset($CFG->$name)) {
			$CFG->$name = 1;
		}
		$enricherstring = get_string('pluginname', 'enricher_'.$enrichername);
		$setting = new admin_setting_configcheckbox($name, $enricherstring, null, 0);
		$settings->add($setting);

    // if enricher contains further settings include them
		if (literature_enricher_check_settings($dirname)) {
			$settingsfile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'enricher' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'settings.php';
			include $settingsfile;
		}

	}


	// settings for SA

	if(!isset($CFG->literature_sa_enabled)) {
			$CFG->literature_sa_enabled = 0;
	}

	$setting = new admin_setting_configcheckbox('literature_sa_enabled', get_string('sa_enabled', 'literature') , null, 0);
	$settings->add($setting);

// should field for mail address appear only if SA enabled?
//	if($CFG->literature_sa_enabled == 1) {
	$setting = new admin_setting_configtext('literature_sa_email_library', get_string('sa_email_library', 'literature'), null, null);
	$settings->add($setting);
		
//	}

	$setting = new admin_setting_configtext('literature_sa_librarylocations', get_string('sa_librarylocations', 'literature'), null, null);
	$settings->add($setting);


}

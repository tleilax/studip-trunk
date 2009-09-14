<?php

/*
 * Copyright (C) 2009 - Andr� Kla�en <aklassen@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'app/controllers/authenticated_controller.php';
require_once 'lib/classes/Seminar.class.php';
require_once 'lib/classes/Modules.class.php';
require_once 'lib/classes/StudygroupAvatar.class.php';
require_once 'app/models/studygroup.php';

// classes required for global-module-settings 
require_once('lib/classes/AdminModules.class.php');
require_once('lib/classes/Config.class.php');

class Course_StudygroupController extends AuthenticatedController {


	function before_filter(&$action, &$args) 
	{
	    parent::before_filter($action, $args);

	    if (Config::GetInstance()->getValue('STUDYGROUPS_ENABLE')
	        || in_array($action, words('globalmodules savemodules deactivate'))) {

    		include 'lib/seminar_open.php';

		// args at position zero is always the studygroup-id
		if ($args[0]) {
    			if (SeminarCategories::GetBySeminarId($args[0])->studygroup_mode == false) {
				throw new Exception(_("Dieses Seminar ist keine Studiengruppe!"));
			}
		}
    		$GLOBALS['CURRENT_PAGE'] =  _('Studiengruppe bearbeiten');
		$GLOBALS['HELP_KEYWORD'] = 'Basis.Studiengruppen';
	    } else {
		throw new Exception(_("Die von Ihnen gew�hlte Option ist im System nicht aktiviert."));
	    }
	}

	/**
	 * shows details of studygroup and actions to join it
	 */
	function details_action( $id ) {
		global $perm;

		$GLOBALS['CURRENT_PAGE'] = getHeaderLine($id).' - '._('Studiengruppendetails');

		$stmt = DBManager::get()->prepare("SELECT * FROM admission_seminar_user 
			WHERE user_id = ? AND seminar_id = ?");
		$stmt->execute(array($GLOBALS['user']->id, $id));
		$data = $stmt->fetch();

		if ($data['status'] == 'accepted') $this->membership_requested = true;

		if ($perm->have_studip_perm('autor',$id)) {
			$this->participant = true;
		} else {
			$this->participant = false;
		}

		$this->studygroup = new Seminar( $id );
		if (!preg_match('/^('.preg_quote($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'],'/').')?([a-zA-Z0-9_-]+\.php)([a-zA-Z0-9_?&=-]*)$/', $_REQUEST['send_from_search_page'])) {
			$this->send_from_search_page = '';
		} else {
			$this->send_from_search_page = $_REQUEST['send_from_search_page'];
		}
	}

	function new_action()
	{
		closeObject();
		$GLOBALS['CURRENT_PAGE'] =  _('Studiengruppe anlegen');
		
		$this->terms = Config::GetInstance()->getValue('STUDYGROUP_TERMS');
		$this->available_modules = StudygroupModel::getAvailableModules();
		if ($GLOBALS['PLUGINS_ENABLE']) {
			$this->available_plugins = StudygroupModel::getAvailablePlugins();
			// $this->enabled_plugins   = StudygroupModel::getEnabledPlugins();
		}
		$this->modules           = new Modules();
	}

	function create_action()
	{
		$errors = array();

		//checks
		if (!Request::get('groupname')) {
			$errors[] = _("Bitte Gruppennamen angeben");
		} else {
			$pdo = DBManager::get();
			$stmt = $pdo->query($query = "SELECT * FROM seminare WHERE name = ". $pdo->quote(Request::get('groupname')));
			if ($stmt->fetch()) {
				$errors[] = _("Eine Veranstaltung/Studiengruppe mit diesem Namen existiert bereits. Bitte w�hlen Sie einen anderen Namen");
			}
		}

		if (!Request::get('grouptermsofuse_ok')) {
			$errors[] = _("Sie m�ssen die Nutzungsbedingungen durch Setzen des H�kchens bei 'Einverstanden' akzeptieren.");
		}
		if (count($errors)) {
			$this->flash['errors'] =  $errors;
			$this->flash['create'] = true;
			$this->flash['request'] = Request::getInstance();
			$this->redirect('course/studygroup/new/');
		} else {
			// Everything seems fine, let's create a studygroup

			$sem = new Seminar();
			$sem->name        = Request::get('groupname');         // seminar-class quotes itself
			$sem->description = Request::get('groupdescription');  // seminar-class quotes itself
			$sem->status      = 99;
			$sem->read_level  = 1;
			$sem->write_level = 1;

			$sem->institut_id = Config::GetInstance()->getValue('STUDYGROUP_DEFAULT_INST');


			$sem->admission_type=0; 
			if (Request::get('groupaccess') == 'all') {
				$sem->admission_prelim = 0;
			} else {
				$sem->admission_prelim = 1;
				$sem->admission_prelim_txt = _("Die ModeratorInnen der Studiengruppe k�nnen Ihren Aufnahmewunsch best�tigen oder ablehnen. Erst nach Best�tigung erhalten Sie vollen Zugriff auf die Gruppe.");
			}
			$sem->admission_endtime=-1;
			$sem->admission_binding=0;
			$sem->admission_starttime=-1;
			$sem->admission_endtime_sem=-1;
			$sem->visible=1;

			$semdata=new SemesterData();
			$this_semester=$semdata->getSemesterDataByDate(time());
			$sem->semester_start_time=$this_semester['beginn'];
			$sem->semester_duration_time=-1;

			$semid=$sem->id;
			$userid=$GLOBALS['auth']->auth['uid'];

			// insert dozent
			DBManager::get()->query("INSERT INTO seminar_user SET ".
			                        "seminar_id='$semid', ".
									"user_id='$userid', ".
									"status='dozent', ".
									"gruppe=8");
			
			// now add the studygroup_dozent dozent who's supposed to be invisible 
			DBManager::get()->query("INSERT INTO seminar_user SET seminar_id='$semid', user_id=MD5('studygroup_dozent'), status='dozent', visible='no'");

			$mods=new Modules();
			$bitmask=0;

			// de-/activate modules
			$available_modules = StudygroupModel::getAvailableModules();

			foreach ($_REQUEST['groupmodule'] as $key => $enable) {
				if ($key=='schedule') continue; // no schedule for studygroups 
				if ($available_modules[$key] && $enable) {
					$mods->setBit($bitmask, $mods->registered_modules[$key]["id"]);
				}
			}
			// always activate participants list
			$mods->setBit($bitmask, $mods->registered_modules["participants"]["id"]);
			
			$sem->modules=$bitmask;
			$sem->store();

			// de-/activate plugins
			$available_plugins = StudygroupModel::getAvailablePlugins();
			foreach ($available_plugins as $key => $name) {
				$plugin = PluginManager::getInstance()->getPlugin($key);
				$plugin->setId($semid);
				if ($_REQUEST['groupplugin'][$key] && $name) {
					$plugin->setActivated(true);
				} else {
					$plugin->setActivated(false);
				}
			}

			// work done. locate to new group.
			$this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $semid));

		}
	}

	function edit_action($id)
	{
		global $perm;
		if ($perm->have_studip_perm('dozent',$id)) {

			$GLOBALS['CURRENT_PAGE'] = getHeaderLine($id).' - '._('Studiengruppe bearbeiten');
			Navigation::activateItem('/course/studygroup/admin');

			$sem                      = new Seminar($id);
			$this->sem_id            = $id;
			$this->sem               = $sem;
			$this->available_modules = StudygroupModel::getAvailableModules();
			if ($GLOBALS['PLUGINS_ENABLE']) {
				$this->available_plugins = StudygroupModel::getAvailablePlugins();
				$this->enabled_plugins   = StudygroupModel::getEnabledPlugins($id);
			}
			$this->modules           = new Modules();
		} else {
			$this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
		}
	}

	function update_action($id)
	{
		global $perm;

		if ($perm->have_studip_perm('dozent',$id)) { 

			$errors = array();

			//checks
			// What kind of checks might be of concern here? 
			if (!Request::get('groupname')) {
				$errors[] = _("Bitte Gruppennamen angeben");
			} else {
				$pdo = DBManager::get();
				$stmt = $pdo->query($query = "SELECT * FROM seminare WHERE name = ". $pdo->quote(Request::get('groupname')) ." AND Seminar_id != ". $pdo->quote( $id ));
				if ($stmt->fetch()) {
					$errors[] = _("Eine Veranstaltung/Studiengruppe mit diesem Namen existiert bereits. Bitte w�hlen Sie einen anderen Namen");
				}
			}

			if (count($errors)) {
				$this->flash['errors'] =  $errors;
				$this->flash['edit'] = true;
				// $this->flash['request'] = $_REQUEST;
				$this->redirect('course/studygroup/edit/' . $id);
			} else {
				// Everything seems fine, let's create a studygroup

				$sem = new Seminar($id);
				$sem->name        = Request::get('groupname');         // seminar-class quotes itself
				$sem->description = Request::get('groupdescription');  // seminar-class quotes itself
				$sem->status      = 99;
				$sem->read_level  = 1;
				$sem->write_level = 1;

				$sem->admission_type = 0; 

				if (Request::get('groupaccess') == 'all') {
					$sem->admission_prelim = 0;
				} else {
					$sem->admission_prelim = 1;
					$sem->admission_prelim_txt = _("Die ModeratorInnen der Studiengruppe k�nnen Ihren Aufnahmewunsch best�tigen oder ablehnen. Erst nach Best�tigung erhalten Sie vollen Zugriff auf die Gruppe.");
				}

				$sem->store();

				$mods=new Modules();
				$bitmask=0;

				// de-/activate modules
				$available_modules = StudygroupModel::getAvailableModules();

				foreach ($_REQUEST['groupmodule'] as $key => $enable) {
					if ($available_modules[$key] && $enable) {
						$mods->setBit($bitmask, $mods->registered_modules[$key]["id"]);
					}
				}

				$sem->modules=$bitmask;
				$mods->writeBin($id, $bitmask, 'sem');

				// de-/activate plugins
				$available_plugins = StudygroupModel::getAvailablePlugins();
				foreach ($available_plugins as $key => $name) {
					$plugin = PluginManager::getInstance()->getPlugin($key);
					$plugin->setId($id);
					if ($_REQUEST['groupplugin'][$key] && $name) {
						$plugin->setActivated(true);
					} else {
						$plugin->setActivated(false);
					}
				}

			}
		}

		$this->redirect('course/studygroup/edit/'. $id);
	}

	function members_action($id)
	{
		$GLOBALS['CURRENT_PAGE'] = getHeaderLine($id) . ' - ' . _("TeilnehmerInnen");
		Navigation::activateItem('/course/members/view');

		$sem=new Seminar($id);

		$this->groupname = $sem->name;
		$this->sem_id = $id;
		$this->groupdescription = $sem->description;
		$this->moderators = $sem->getMembers('dozent');
		unset($this->moderators[md5('studygroup_dozent')]);
		$this->tutors =  $sem->getMembers('tutor');
		$this->members = array_merge($this->moderators, $this->tutors, $sem->getMembers('autor'));
		$this->accepted = $sem->getAdmissionMembers('accepted');
		$this->rechte = $GLOBALS['perm']->have_studip_perm("tutor", $id);
	}

	function edit_members_action($id,$user,$status,$stat='')
	{
		global $perm;
		if ($perm->have_studip_perm('tutor',$id)) {

			if (!$status) {
				$this->flash['success'] = _("Es wurde keine korrekte Option gew�hlt.");
			} elseif ($status == 'accept') {
				StudygroupModel::accept_user($user,$id);
				$this->flash['success'] = sprintf(_("Der Nutzer %s wurde akzeptiert."), get_fullname_from_uname($user));
			} elseif ($status == 'deny') {
				StudygroupModel::deny_user($user,$id);
				$this->flash['success'] = sprintf(_("Der Nutzer %s wurde nicht akzeptiert."), get_fullname_from_uname($user));
			}

			if ($perm->have_studip_perm('dozent', $id)) {
				if ($status == 'promote' && $perm !='') {
					StudygroupModel::promote_user($user,$id,$stat);
					$this->flash['success'] = sprintf(_("Der Status des Nutzer %s wurde ge�ndert."), get_fullname_from_uname($user));
				} elseif ($status == 'remove') {
					StudygroupModel::remove_user($user,$id);
					$this->flash['success'] = sprintf(_("Der Nutzer %s wurde aus der Studiengruppe entfernt."), get_fullname_from_uname($user));
				}
			}

			$this->redirect('course/studygroup/members/'.$id);
		}   else {
			$this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
		}
	}

	function delete_action($id, $approveDelete = false, $studipticket = false)
	{
		global $perm;
		if ($perm->have_studip_perm( 'dozent',$id )) {

			if ($approveDelete && check_ticket($studipticket)) {
				$messages = array();
				$sem=new Seminar($id);
	            $sem->delete();
          	
          	
    	      	if ($messages = $sem->getStackedMessages()) {
	    			$this->flash['messages'] = $messages;
	    		}
	    		unset($sem);
			
				$this->redirect('course/studygroup/new');
			} else if (!$approveDelete) {
				$template = $GLOBALS['template_factory']->open('shared/question');

				$template->set_attribute('approvalLink', $this->url_for('/course/studygroup/delete/'. $id. '/true/'. get_ticket()));
				$template->set_attribute('disapprovalLink', $this->url_for('/course/studygroup/edit/'. $id));
				$template->set_attribute('question', _("Sind Sie sicher, dass Sie diese Studiengruppe l�schen m�chten?"));

				$this->flash['question'] = $template->render();
				$this->redirect('course/studygroup/edit/'. $id);
			} else {
				$this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
			}
		} else {
			$this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
		}

	}


	/**
	 * Globale Einstellungen -> Studiengruppen. Hier wird die Ansicht gebaut.
	 */
	function globalmodules_action() {
		global $perm;
		$perm->check("root");
		$GLOBALS['HELP_KEYWORD'] = 'Admin.Studiengruppen';
		
		// get available modules
		$modules = StudygroupModel::getInstalledModules() + StudygroupModel::getInstalledPlugins();
		$enabled = StudygroupModel::getAvailability( $modules );

		// get institutes
		$institutes = StudygroupModel::getInstitutes();
		$default_inst = Config::GetInstance()->getValue('STUDYGROUP_DEFAULT_INST');

		// Nutzungsbedingungen
		$terms = Config::GetInstance()->getValue('STUDYGROUP_TERMS');

		$GLOBALS['CURRENT_PAGE'] = _('Verwaltung studentischer Arbeitsgruppen');
		Navigation::activateItem('/admin/config/studygroup');

		// set variables for view
		$this->current_page = _("Verwaltung erlaubter Module und Plugins f�r Studiengruppen");
		$this->modules      = $modules;
		$this->enabled      = $enabled;
		$this->institutes   = $institutes;
		$this->default_inst = $default_inst;
		$this->terms        = $terms;
	}
	
	/**
	 * Globale Einstellungen -> Studiengruppen. Hier werden die Einstellungen gespeichert
	 * und danach wird weitergeleitet zur globalmodules_action
	 */
	function savemodules_action() {
		global $perm;
		$perm->check("root");
		$GLOBALS['HELP_KEYWORD'] = 'Admin.Studiengruppen';
		
		$err=0;
		if (Request::quoted('institute')=='invalid') $err=1;
		if (Request::quoted('terms')=='invalid') $err=1;
		foreach ($_REQUEST['modules'] as $key => $value) 
			if ($value=='invalid') $err=1;
		
		if ($err) {
			$this->flash['error'] = _("Fehler beim Speichern der Einstellung!");
		} else {				
			$cfg=new Config("STUDYGROUPS_ENABLE");
			if ($cfg->getValue()==FALSE) {
				$cfg->setValue(TRUE,"STUDYGROUPS_ENABLE","Studiengruppen");
				$this->flash['success'] = _("Die Studiengruppen wurden aktiviert.");
			}

			if ( is_array($_REQUEST['modules']) ) {
				// $config_string enth�lt modul/pluginname=0/1|...
				foreach ($_REQUEST['modules'] as $key => $value) {
					if (in_array($key, array('participants','schedule'))) continue;
					$config_string[] = $key .':'. ($value=='on'?'1':'0');
				}
				$config_string[] = 'participants:1';
				$config_string[] = 'schedule:0';

				Config::GetInstance()->setValue(implode('|', $config_string), 'STUDYGROUP_SETTINGS');
				Config::GetInstance()->setValue( Request::quoted('institute'), 'STUDYGROUP_DEFAULT_INST');
				Config::GetInstance()->setValue( Request::quoted('terms'), 'STUDYGROUP_TERMS');
				$this->flash['success'] = _("Die Einstellungen wurden gespeichert!");
			} else {
				$this->flash['error'] = _("Fehler beim Speichern der Einstellung!");
			}
		}
		$this->redirect('course/studygroup/globalmodules');
	}
	
	function deactivate_action() {
		global $perm;
		$perm->check("root");
		$GLOBALS['HELP_KEYWORD'] = 'Admin.Studiengruppen';
		$cfg=new Config();
		$cfg->setValue(FALSE,"STUDYGROUPS_ENABLE","Studiengruppen");
		$this->flash['success'] = _("Die Studiengruppen wurden deaktiviert.");
		$this->redirect('course/studygroup/globalmodules');
	}
	
	function search_action() {
		$GLOBALS['CURRENT_PAGE'] =  _('Studiengruppen suchen');
		Navigation::activateItem('/browse/studygroups/all');
		$this->groups = StudygroupModel::getAllGroups();
		$this->userid = $GLOBALS['auth']->auth['uid'];
	}
}

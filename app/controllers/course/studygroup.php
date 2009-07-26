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
require_once 'app/models/studygroup.php';

// classes required for global-module-settings 
require_once('lib/classes/AdminModules.class.php');
require_once('lib/classes/Config.class.php');

class Course_StudygroupController extends AuthenticatedController {


	function before_filter(&$action, &$args) 
	{
		parent::before_filter($action, $args);
		include 'lib/seminar_open.php';

		$this->tabs = 'links_openobject';
	}

	/**
	 * shows details of studygroup and actions to join it
	 */
	function details_action( $id ) {
		global $perm;

		$GLOBALS['CURRENT_PAGE'] = getHeaderLine($id).' - '._('Arbeitsgruppendetails');

		if ($perm->have_studip_perm('autor',$id)) {
			$this->participant = true;
		} else {
			$this->participant = false;
			unset($this->tabs);
		}

		$this->studygroup = new Seminar( $id );
		if (!preg_match('/^('.preg_quote($CANONICAL_RELATIVE_PATH_STUDIP,'/').')?([a-zA-Z0-9_-]+\.php)([a-zA-Z0-9_?&=-]*)$/', $send_from_search_page)) $send_from_search_page = '';
		$this->send_from_search_page = $send_from_search_page;
	}

	function new_action()
	{
		$GLOBALS['CURRENT_PAGE'] =  _('Arbeitsgruppe anlegen');

		$this->terms = Config::GetInstance()->getValue('STUDYGROUP_TERMS');
	}

	function create_action()
	{
		$errors = array();

		//checks
		if (!$_REQUEST['groupname']) {
			$errors[] = _("Bitte Gruppennamen angeben");
		} else {
			$db=new DB_Seminar();
			$db->query("SELECT * FROM seminare WHERE name='".$_REQUEST['groupname']."'");
			if ($db->nf()) {
				$errors[] = _("Eine Veranstaltung/Arbeitsgruppe mit diesem Namen existiert bereits. Bitte w�hlen Sie einen anderen Namen");
			}
		}
		if (!$_REQUEST['grouptermsofuse_ok']) {
			$errors[] = _("Sie m�ssen die Nutzungsbedingungen durch Setzen des H�kchens bei 'Einverstanden' akzeptieren.");
		}
		if (count($errors)) {
			$this->flash['errors'] =  $errors;
			$this->flash['create'] = true;
			$this->flash['request'] = $_REQUEST;
			$this->redirect('course/studygroup/new/');
		} else {
			// Everything seems fine, let's create a studygroup

			$sem=new Seminar();
			$sem->name=$_REQUEST['groupname'];
			$sem->description=$_REQUEST['groupdescription'];
			$sem->status=99;
			$sem->read_level=1;
			$sem->write_level=1;

			$sem->institute_id = Config::GetInstance()->getValue('STUDYGROUP_DEFAULT_INST');


			$sem->admission_type=0; 
			if ($_REQUEST['groupaccess']=='all') {
				$sem->admission_prelim=0;
			} else {
				$sem->admission_prelim=1;
				$sem->admission_prelim_txt=_("Die ModeratorInnen der Arbeitsgruppe k�nnen Ihren Aufnahmewunsch best�tigen oder ablehnen. Erst nach Best�tigung erhalten Sie vollen Zugriff auf die Gruppe.");
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
			$sem->institut_id=''; // TODO: default inst id!

			$sem->store();
			$semid=$sem->id;
			$userid=$GLOBALS['auth']->auth['uid'];

			// insert dozent
			$q="INSERT INTO seminar_user SET seminar_id='$semid', user_id='$userid', status='dozent'";
			$db=new DB_Seminar();
			$db->query($q);
            
            // now add the studygroup_dozent dozent who's supposed to be invisible 
            $q="INSERT INTO seminar_user SET seminar_id='$semid', user_id=MD5('studygroup_dozent'), status='dozent', visible='no'";
            $db->query($q);
            
			$mods=new Modules();
			$bitmask=0;
			if ($_REQUEST['groupmodule_forum']) {
				$mods->setBit($bitmask, $mods->registered_modules["forum"]["id"]);
			}
			if ($_REQUEST['groupmodule_files']) {
				$mods->setBit($bitmask, $mods->registered_modules["documents"]["id"]);
			}
			#if ($_REQUEST['groupmodule_members']) {
			$mods->setBit($bitmask, $mods->registered_modules["participants"]["id"]);
			#}
			if ($_REQUEST['groupmodule_wiki']) {
				$mods->setBit($bitmask, $mods->registered_modules["wiki"]["id"]);
			}
			if ($_REQUEST['groupmodule_literature']) {
				$mods->setBit($bitmask, $mods->registered_modules["literature"]["id"]);
			}
			$sem->modules=$bitmask;
			$mods->writeBin($semid, $bitmask, 'sem');


			// work done. locate to new group.
			$this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $semid));

		}
	}

	function edit_action($id)
	{
		global $perm;
		if ($perm->have_studip_perm('dozent',$id)) {

			$this->reiter_view = '_studygroup_admin';
			$GLOBALS['CURRENT_PAGE'] = getHeaderLine($id).' - '._('Arbeitsgruppe bearbeiten');
			$sem                      = new Seminar($id);
			$this->sem_id            = $id;
			$this->sem               = $sem;
			$this->available_modules = StudygroupModel::getAvailableModules();
			if ($GLOBALS['PLUGINS_ENABLE']) {
				$this->available_plugins = StudygroupModel::getAvailablePlugins();
				$this->enabled_plugins   = StudygroupModel::getEnabledPlugins();
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
			if (!$_REQUEST['groupname']) {
				$errors[] = _("Bitte Gruppennamen angeben");
			} else {
				$db=new DB_Seminar();
				$db->query("SELECT * FROM seminare WHERE name='".$_REQUEST['groupname']."' AND Seminar_id != '".$id."' ");
				if ($db->nf()) {
					$errors[] = _("Eine Veranstaltung/Arbeitsgruppe mit diesem Namen existiert bereits. Bitte w�hlen Sie einen anderen Namen");
				}
			}

			if (count($errors)) {
				$this->flash['errors'] =  $errors;
				$this->flash['edit'] = true;
				$this->flash['request'] = $_REQUEST;
				$this->redirect('course/studygroup/edit/' . $id);
			} else {
				// Everything seems fine, let's create a studygroup

				$sem=new Seminar($id);
				$sem->name=$_REQUEST['groupname'];
				$sem->description=$_REQUEST['groupdescription'];
				$sem->status=99;
				$sem->read_level=1;
				$sem->write_level=1;

				$sem->admission_type=0; 

				if ($_REQUEST['groupaccess']=='all') {
					$sem->admission_prelim=0;
				} else {
					$sem->admission_prelim=1;
					$sem->admission_prelim_txt=_("Die ModeratorInnen der Arbeitsgruppe k�nnen Ihren Aufnahmewunsch best�tigen oder ablehnen. Erst nach Best�tigung erhalten Sie vollen Zugriff auf die Gruppe.");
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
					if ($_REQUEST['groupplugin'][$key] && $enable) {
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
		$GLOBALS['CURRENT_PAGE'] = getHeaderLine($id).' - '.'TeilnehmerInnen';
		$this->reiter_view = '_studygroup_teilnehmer';

		$sem=new Seminar($id);

		$this->groupname = $sem->name;
		$this->sem_id = $id;
		$this->groupdescription = $sem->description;
		$this->moderators =$sem->getMembers('dozent');
		$this->tutors =  $sem->getMembers('tutor');
		$this->members = array_merge($sem->getMembers('dozent'), $sem->getMembers('tutor'), $sem->getMembers('autor'));
		$this->accepted = $sem->getAdmissionMembers('accepted');
		$this->rechte =  $GLOBALS['rechte']; 
	}

	function edit_members_action($id,$user,$status,$stat='')
	{
		global $perm;
		if ($perm->have_studip_perm('tutor',$id)) {

			if (!$status) {
				$this->flash['success'] = _("Es wurde keine korrekte Option gew�hlt.");
			} elseif ($status == 'accept') {
				accept_user($user,$id);
				$this->flash['success'] = sprintf(_("Der Nutzer %s wurde akzeptiert."), get_fullname($user));
			} elseif ($status == 'deny') {
				deny_user($user,$id);
				$this->flash['success'] = sprintf(_("Der Nutzer %s wurde nicht akzeptiert."), get_fullname($user));
			} elseif ($status == 'promote' && $perm !='') {

				promote_user($user,$id,$stat);
				$this->flash['success'] = sprintf(_("Der Status des Nutzer %s wurde ge�ndert."), get_fullname($user));
			} elseif ($status == 'remove') {

				remove_user($user,$id);
				$this->flash['success'] = sprintf(_("Der Nutzer %s wurde aus der Studiengruppe entfernt."), get_fullname($user));
			}

			$this->redirect('course/studygroup/members/'.$id);
		}   else {
			$this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
		}
	}

	function delete_action($id)
	{
		global $perm;
		if($perm->have_studip_perm('dozent',$id)) {

			$messages = array();

			$sem=new Seminar($id);
			
            $sem->delete();
          	
          	
          	if ($messages = $sem->getStackedMessages()) {
    			$this->flash['messages'] = $messages;
    		}
    		unset($sem);
			
			$this->redirect('course/studygroup/new/');
		} else {
			$this->redirect(URLHelper::getURL('seminar_main.php?auswahl=' . $id));
		}

	}


	/**
	 * Globale Einstellungen -> Studentische Arbeitsgruppen. Hier wird die Ansicht gebaut.
	 */
	function globalmodules_action() {
		global $perm;
		$perm->check("root");

		// get available modules
		$modules = StudygroupModel::getInstalledModules() + StudygroupModel::getInstalledPlugins();
		$enabled = StudygroupModel::getAvailability( $modules );

		// get institutes
		$institutes = StudygroupModel::getInstitutes();
		$default_inst = Config::GetInstance()->getValue('STUDYGROUP_DEFAULT_INST');

		// Nutzungsbedingungen
		$terms = Config::GetInstance()->getValue('STUDYGROUP_TERMS');


		// set variables for view
		$this->current_page = _("Verwaltung erlaubter Module und Plugins f�r Studentische Arbeitsgruppen");
		$this->tabs         = 'links_admin';
		$this->modules      = $modules;
		$this->enabled      = $enabled;
		$this->institutes   = $institutes;
		$this->default_inst = $default_inst;
		$this->terms        = $terms;
		$this->reiter_view  = 'admin_studygroup';

	}
	
	/**
	 * Globale Einstellungen -> Studentische Arbeitsgruppen. Hier werden die Einstellungen gespeichert
	 * und danach wird weitergeleitet zur globalmodules_action
	 */
	function savemodules_action() {
		if ( is_array($_REQUEST['modules']) ) {
			foreach ($_REQUEST['modules'] as $key => $value) {
				$config_string[] = $key .':'. $value;
			}
			Config::GetInstance()->setValue(implode('|', $config_string), 'STUDYGROUP_SETTINGS');
			Config::GetInstance()->setValue( Request::get('institute'), 'STUDYGROUP_DEFAULT_INST');
			Config::GetInstance()->setValue( Request::get('terms'), 'STUDYGROUP_TERMS');
			$this->flash['success'] = _("Die Einstellungen wurden gespeichert!");
		} else {
			$this->flash['error'] = _("Fehler beim Speichern der Einstellung!");
		}

		$this->redirect('course/studygroup/globalmodules');
	}
}

<?
/**
* request_new_password.php
*
* Handles password requests and sends a new password to the users email address.
*
*
* @author		Mike Barthel <m.barthel.goe@gmx.de>, Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id: request_new_password.php 7206 2007-01-24 22:07:49Z schmelzer $
* @access		public
* @module		request_new_password.php
* @modulegroup	public
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// request_new_password.php
// Handles password requests and sends a new password to the users email address
//
// Copyright (C) 2007 Mike Barthel <m.barthel.goe@gmx.de>,
// Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

page_open(array('sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User'));

if (!$GLOBALS['ENABLE_SELF_REGISTRATION'] || !$GLOBALS['ENABLE_REQUEST_NEW_PASSWORD_BY_USER']) {
	require_once ('lib/msg.inc.php');
	// Start of Output
	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head
	$message = _("Das Anfordern eines neuen Passwortes durch den Benutzer ist in dieser Stud.IP-Installation nicht m�glich.");
	parse_window ("error�$message", "�", _("Passwortanforderung nicht m�glich!"));
	include ('lib/include/html_end.inc.php');
	die();
}

require_once('lib/language.inc.php');
require_once('lib/visual.inc.php');
require_once('config.inc.php');
require_once('lib/classes/HeaderController.class.php');

if (!isset($_language)) {
	$_language = get_accepted_languages();
}
$_language_path = init_i18n($_language);
require_once('lib/msg.inc.php');
include('lib/classes/UserManagement.class.php');


class UserManagementRequestNewPassword extends UserManagement {
	
	function UserManagementRequestNewPassword ($user_id) {
		parent::UserManagement($user_id);
	}
	
	function setPassword () {

		// Can we reach the email?
		if (!$this->checkMail($this->user_data['auth_user_md5.Email'])) {
			return FALSE;
		}
		
		$password = $this->generate_password(6);
		$this->user_data['auth_user_md5.password'] = md5($password);

		if (!$this->storeToDatabase()) {
			$this->msg .= "error�" . _("Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden.") . "�";
			return FALSE;
		}
		
		$this->msg .= "msg�" . sprintf(_("Passwort von User \"%s\" neu gesetzt."), $this->user_data['auth_user_md5.username']) . "�";

		// include language-specific subject and mailbody
		$user_language = getUserLanguagePath($this->user_data['auth_user_md5.user_id']);
		$Zeit=date("H:i:s, d.m.Y",time());
		include("locale/$user_language/LC_MAILS/password_mail.inc.php");

		// send mail
		$this->smtp->SendMessage(
				$this->smtp->env_from,
				array($this->user_data['auth_user_md5.Email']),
				array("From: " . $this->smtp->from,
						"Reply-To:" . $this->smtp->abuse,
						"To: " . $this->user_data['auth_user_md5.Email'],
						"Subject: " . $subject),
				$mailbody);
		
		log_event("USER_NEWPWD",$this->user_data['auth_user_md5.user_id']);
		return TRUE;

	}
}

$msg = array();
$admin_link = sprintf(_("Leider ist ein Fehler aufgetreten. Bitte fordern Sie gegebenenfalls %sper E-Mail%s ein neues Passwort an."), "<a href=\"mailto:{$GLOBALS['UNI_CONTACT']}?subject=" . rawurlencode( "Stud.IP Passwort vergessen - {$GLOBALS['UNI_NAME_CLEAN']}" ) . "&amp;body=" . rawurlencode( "Ich habe mein Passwort vergessen. Bitte senden Sie mir ein Neues.\nMein Nutzername: " . htmlReady( $uname ) . "\n" ) . "\">", "</a>");


/*
	######################################################
	### Formularauswertung: Eingabe der E-Mail-Adresse ###
	######################################################
*/
if( $_POST['email'] != "" ) {
	$email = trim( stripslashes( $_POST['email'] ) );
	$validator =& new email_validation_class();
	if( !$validator->ValidateEmailAddress( $email ) ) {
		// E-Mail ung�ltig
		$msg[] = array( 'error', _("Die E-Mail-Adresse ist ung�ltig!") . '</br>' );
	} else {
		// Suche Benutzer �ber E-Mail-Adresse
		$email = mysql_escape_string( $email );
	    $db =& new DB_Seminar();
	    $db->query( "SELECT user_id, username, Vorname, Nachname, Email, IFNULL(auth_plugin, 'standard') AS auth_plugin FROM auth_user_md5 WHERE Email='{$email}'" );
	    if( $db->num_rows() == 0 ) {
	    	// kein Benutzer mit eingegebener E-Mail
	    	$msg[] = array( 'error', _("Es konnte kein Benutzer mit dieser E-Mail-Adresse<br/>gefunden werden!"));
	    	$msg[] = array('info', $admin_link);
	    } elseif( $db->num_rows() == 1 ) {
			$db->next_record();
			if (strtolower($db->f('auth_plugin')) != 'standard') {
				$msg[] = array('error', sprintf(_("Ihr Passwort kann nur durch einen Adminstrator ge&auml;ndert werden. Bitte fordern Sie gegebenenfalls %sper E-Mail%s ein neues Passwort an."), "<a href=\"mailto:{$GLOBALS['UNI_CONTACT']}?subject=" . rawurlencode( "Stud.IP Passwort vergessen - {$GLOBALS['UNI_NAME_CLEAN']}" ) . "&amp;body=" . rawurlencode( "Ich habe mein Passwort vergessen. Bitte senden Sie mir ein Neues.\nMein Nutzername: " . htmlReady( $uname ) . "\n" ) . "\">", "</a>"));
			} else {
				// Best�tigungslink senden
				$step = 2;
				$msg[] = array( 'info', sprintf(_("In K�rze wird Ihnen eine E-Mail an die Adresse %s mit einem Best�tigungslink geschickt. Bitte beachten Sie die Hinweise in dieser E-Mail. Sollte Sie keine E-Mail erhalten haben, vergewissern Sie sich, ob diese evtl. in einem Spam-Ordner abgelegt wurde."), $db->f('Email')));
				$username = $db->f('username');
				$vorname  = $db->f('Vorname');
				$nachname = $db->f('Nachname');
				$id = md5($username . $GLOBALS['REQUEST_NEW_PASSWORD_SECRET');

				$smtp =& new studip_smtp_class();
				// include language-specific subject and mailbody
				$user_language = getUserLanguagePath($db->f('user_id'));
				$Zeit=date("H:i:s, d.m.Y",time());
				include("locale/$user_language/LC_MAILS/request_new_password_mail.inc.php");
				
				$smtp->SendMessage($smtp->env_from, array($db->f('Email')), array("From: ".$smtp->from, "To: ".$db->f('Email'), "Reply-To: ".$db->f('Email'), "Subject: {$subject}"), $mailbody);
			}
	    } else {
			// Mehrere Benutzer f�r E-Mail
	    	$msg[] = array( 'error', _("Diese E-Mail-Adresse wird von mehreren Benutzern genutzt!"));
	    	$msg[] = array('info', $admin_link);
	    }
	}
} else {
	// E-Mail leer
	if ($_POST['step']) {
		$msg[] = array( 'error', _("Sie haben keine E-Mail-Adresse eingegeben!" ) );
	}
}

/*
	#################################################
	### Auswerten des Best�tigungslinks           ###
	#################################################
*/
if ($_GET['id'] != '') {
	$step = 4;
	if ($_GET['uname'] != '') {
		$username = trim($_GET['uname']);
		$username = mysql_escape_string($username);
		$db =& new DB_Seminar();
		$db->query( "SELECT user_id FROM auth_user_md5 WHERE username='{$username}'" );
		if ($db->num_rows() == 1 && trim($_GET['id']) == md5($username . $GLOBALS['REQUEST_NEW_PASSWORD_SECRET'])) {
			$db->next_record();
			$user_management =& new UserManagementRequestNewPassword($db->f('user_id'));
			if ($user_management->setPassword()) {
				$msg[] = array( 'msg', sprintf(_("Ihnen wird in K�rze eine E-Mail an die Adresse %s mit Ihrem neuen Passwort geschickt. Bitte beachten Sie die Hinweise in dieser E-Mail."), $user_management->user_data['auth_user_md5.Email']));
			} else {
				$msg[] = array( 'error', _("Das Passwort konnte nicht gesetzt werden. Bitte wiederholen Sie den Vorgang oder fordern Sie ein neues Passwort per E-Mail an."));
				$msg[] = array('info', $admin_link);
			}
		} else {
			$msg[] = array( 'error', _("Fehler beim Aufruf dieser Seite. Stellen Sie sicher, dass sie den gesamten Link in die Adressleiste eingetragen haben. Bitte wiederholen Sie den Vorgang oder fordern Sie ein neues Passwort per E-Mail an."));
			$msg[] = array('info', $admin_link);
		}
	} else {
		$msg[] = array( 'error', _("Fehler beim Aufruf dieser Seite. Bitte wiederholen Sie den Vorgang oder fordern Sie ein neues Passwort per E-Mail an."));
		$msg[] = array('info', $admin_link);
	}
}

if (!$_POST['step'] && !isset($step)) {
	$step = 1;
}

$request_template =& $GLOBALS['template_factory']->open('request_password');
$request_template->set_attribute('step', $step);
$request_template->set_attribute('messages', $msg);
$request_template->set_attribute('link_startpage', sprintf(_("Zur�ck zur %sStartseite%s."), '<a href="./index.php?cancel_login=1">', '</a>'));
$request_template->set_attribute('email', $email);

$header_controller = new HeaderController();
$header_controller->help_keyword = 'Basis.AnmeldungPasswortAnfrage';
$header_controller->current_page = _("Passwort anfordern");
$header_template =& $GLOBALS['template_factory']->open('header');
$header_controller->fillTemplate($header_template);

include('lib/include/html_head.inc.php');
echo $header_template->render();
echo $request_template->render();
include 'lib/include/html_end.inc.php';

page_close()
?>

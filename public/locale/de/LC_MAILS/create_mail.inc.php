<?
		$subject="Anmeldung Stud.IP-System";
		
		$mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
		."(Studienbegleitender Internetsupport von Pr�senzlehre)\n"
		."- " . $GLOBALS['UNI_NAME_CLEAN'] . " -\n\n"
		."Sie wurden um " . $Zeit . " mit folgenden Angaben von einem\n"
		."der Administrierenden in das System eingetragen:\n\n"
		."Benutzername: " . $this->user_data['auth_user_md5.username'] . "\n"
		."Passwort: " . $password . "\n"
		."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
		."Vorname: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
		."Nachname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
		."E-Mail-Adresse: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
		."Diese Mail wurde Ihnen zugesandt, um Ihnen den Benutzernamen\n"
		."und das Passwort mitzuteilen, mit dem Sie sich am System anmelden k�nnen.\n\n"
		."Sie finden die Startseite des Systems unter folgender URL:\n\n"
		. $this->smtp->url . "\n\n"
		."Wahrscheinlich unterst�tzt Ihr Mail-Programm ein einfaches Anklicken des Links.\n"
		."Ansonsten m�ssen Sie Ihren Browser �ffnen und den Link komplett in die Zeile\n"
		."\"Location\" oder \"URL\" kopieren.\n\n"
		."Um Zugang auf die nicht�ffentlichen Bereiche des Systems zu bekommen\n"
		."m�ssen Sie sich unter \"Login\" auf der Seite anmelden.\n"
		."Geben Sie bitte unter Benutzername \"" . $this->user_data['auth_user_md5.username'] . "\" und unter\n"
		."Passwort \"" . $password . "\" ein.\n\n"
		."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
		."weiter (auch nicht an eine Administratorin oder einen Administrator),\n"
		."damit nicht Dritte in Ihrem Namen Nachrichten\n"
		."in das System einstellen k�nnen!\n\n";

?>

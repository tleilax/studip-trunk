<?
		$subject="Anmeldung Stud.IP-System";
		
		$mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
		."(Studienbegleitender Internetsupport von Pr�senzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Sie wurden um $Zeit mit folgenden Angaben von einem\n"
		."der Administratoren ins System eingetragen:\n\n"
		."Benutzername: $username\n"
		."Passwort: $password\n"
		."Status: $permlist\n"
		."Vorname: $Vorname\n"
		."Nachname: $Nachname\n"
		."E-Mail-Adresse: $Email\n\n"
		."Diese Mail wurde Ihnen zugesandt um Ihnen den Benutzernamen\n"
		."und das Passwort mitzuteilen, mit dem Sie sich am System anmelden.\n\n"
		."Sie finden die Startseite des Systems unter folgender URL:\n\n"
		."$url\n\n"
		."Wahrscheinlich unterst�tzt ihr Mail-Programm ein einfaches Anklicken des Links.\n"
		."Ansonsten m�ssen sie Ihren Browser �ffnen und den Link komplett in die Zeile\n"
		."\"Location\" oder \"URL\" kopieren.\n\n"
		."Um Zugang auf die nicht�ffentlichen Bereiche des Systems zu bekommen\n"
		."m�ssen Sie sich unter \"Login\" oben rechts auf der Seite anmelden.\n"
		."Geben Sie bitte unter Benutzername \"$username\" und unter\n"
		."Passwort \"$password\" ein.\n\n"
		."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
		."weiter (auch nicht an einen Administrator), damit nicht Dritte in ihrem\n"
		."Namen Nachrichten in das System einstellen k�nnen!\n\n";

?>

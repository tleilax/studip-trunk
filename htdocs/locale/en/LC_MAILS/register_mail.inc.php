<?
		$subject="Best�tigungsmail des Stud.IP-Systems";
		
		$mailbody="Dies ist eine Best�tigungsmail des Systems\n"
		."\"Studienbegleitender Internetsupport Pr�senzlehre\"\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Sie haben sich um $Zeit mit folgenden Angaben angemeldet:\n\n"
		."Benutzername: $username\n"
		."Vorname: $Vorname\n"
		."Nachname: $Nachname\n"
		."Email-Adresse: $Email\n\n"
		."Diese Mail wurde Ihnen zugesandt um sicherzustellen,\n"
		."da� die angegebene Email-Adresse tats�chlich Ihnen geh�rt.\n\n"
		."Wenn diese Angaben korrekt sind, dann �ffnen Sie bitte den Link\n\n"
		."$url\n\n"
		."in Ihrem Browser.\n"
		."M�glicherweise unterst�tzt ihr Mail-Programm ein einfaches Anklicken des Links.\n"
		."Ansonsten m�ssen sie Ihren Browser �ffnen und den Link komplett in die Zeile\n"
		."\"Location\" oder \"URL\" kopieren.\n\n"
		."Sie m�ssen sich auf jeden Fall als Benutzer \"$username\" anmelden,\n"
		."damit die R�ckbest�tigung funktioniert.\n\n"
		."Falls Sie sich nicht als Benutzer \"$username\" angemeldet haben\n"
		."oder �berhaupt nicht wissen, wovon hier die Rede ist,\n"
		."dann hat jemand Ihre Email-Adresse missbraucht!\n\n"
		."Bitte wenden Sie sich in diesem Fall an $smtp->abuse,\n"
		."damit der Eintrag aus der Datenbank gel�scht wird.\n";
?>

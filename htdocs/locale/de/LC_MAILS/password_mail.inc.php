<?
		$subject="Passwort-�nderung Stud.IP-System";
		
		$mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
		."(Studienbegleitender Internetsupport von Pr�senzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Ihr Passwort wurde um $Zeit von einem der Administratoren neu gesetzt.\n"
		."Die aktuellen Angaben lauten:\n\n"
		."Benutzername: $username\n"
		."Passwort: $password\n"
		."Status: $permlist\n"
		."Vorname: $Vorname\n"
		."Nachname: $Nachname\n"
		."E-Mail-Adresse: $Email\n\n"
		."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
		."weiter (auch nicht an einen Administrator), damit nicht Dritte in ihrem\n"
		."Namen Nachrichten in das System einstellen k�nnen!\n\n"
		."Hier kommen Sie direkt ins System:\n"
		."$url\n\n";

?>

<?
		$subject="Account-�nderung Stud.IP-System";
		
		$mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
		."(Studienbegleitender Internetsupport von Pr�senzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Ihr Account wurde um $Zeit von einem der Administratoren ver�ndert.\n"
		."Die aktuellen Angaben lauten:\n\n"
		."Benutzername: $username\n"
		."Status: $permlist\n"
		."Vorname: $Vorname\n"
		."Nachname: $Nachname\n"
		."E-Mail-Adresse: $Email\n\n"
		."Ihr Passwort hat sich nicht ver�ndert.\n\n"
		."Diese Mail wurde Ihnen zugesandt um Sie �ber die �nderungen zu informieren.\n\n"
		."Wenn Sie Einw�nde gegen die �nderungen haben, wenden Sie sich bitte an\n"
		."$smtp->abuse. Sie k�nnen einfach auf diese Mail antworten.\n\n"
		."Hier kommen Sie direkt ins System:\n"
		."$url\n\n";

?>

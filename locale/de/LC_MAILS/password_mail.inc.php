<?
        $subject="Passwort-�nderung Stud.IP-System";
        
        $mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
        ."(Studienbegleitender Internetsupport von Pr�senzlehre)\n"
        ."- " . $GLOBALS['UNI_NAME_CLEAN'] . " -\n\n"
        ."Ihr Passwort wurde um " . $Zeit . " von einem der Administrierenden neu gesetzt.\n"
        ."Die aktuellen Angaben lauten:\n\n"
        ."Benutzername: " . $this->user_data['auth_user_md5.username'] . "\n"
        ."Passwort: " . $password . "\n"
        ."Status: " . $this->user_data['auth_user_md5.perms'] . "\n"
        ."Vorname: " . $this->user_data['auth_user_md5.Vorname'] . "\n"
        ."Nachname: " . $this->user_data['auth_user_md5.Nachname'] . "\n"
        ."E-Mail-Adresse: " . $this->user_data['auth_user_md5.Email'] . "\n\n"
        ."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
        ."weiter (auch nicht an eine Administratorin oder einen Administrator),\n"
        ."damit nicht Dritte in Ihrem Namen Nachrichten\n"
        ."in das System einstellen k�nnen!\n\n"
        ."Hier kommen Sie direkt ins System:\n"
        . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\n\n"

?>

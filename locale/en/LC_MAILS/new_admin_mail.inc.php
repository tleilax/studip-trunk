<?
        $subject = "New administrator in your institution";
        
        $mailbody = sprintf("Dear %s %s,\n\n"
        ."%s %s has been registered as an administrator in the institution '%s' and will support you from now on in handling with StudIP.",$db->f('Vorname'),$db->f('Nachname'),$vorname,$nachname,$instname);
        
?>

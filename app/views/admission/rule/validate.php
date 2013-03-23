<?php
if ($errors) {
    if ($via_ajax) {
        $errors = array_map('utf8_encode', $errors);
    }
    echo MessageBox::error(_('Fehler:'), $errors);
}
?>
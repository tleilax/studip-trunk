<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<form action="<?= $controller->url_for('document/testing/index') ?>" method="post" enctype="multipart/form-data"  >
    <h3><?=_('Hochladen einer Datei')?></h3>
    <input type="file" name="datei"><br>
    <?= Studip\Button::createAccept(_('Hochladen'), 'create') ?>
    <h3><?=_('Ordner erstellen')?></h3>
    <?= Studip\Button::createAccept(_('neuer Ordner'), 'mkdir') ?>
    <h3><?=_('Anzeigen der Inhalte')?></h3>
    <?= Studip\Button::createAccept(_('Anzeigen'), 'list') ?>
        <?=$this->test?>
    <h3><?=_('komplett test')?></h3>
    <?= Studip\Button::createAccept(_('TEST'), 'test') ?>
</form>

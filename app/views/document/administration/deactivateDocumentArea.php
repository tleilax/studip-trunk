<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->url_for('document/administration/deactivateDocumentArea/'.$config_id) ?>"
      method="post" class="studip_form">
    <? if(isset($header)) : ?>
        <h3>
            <?= $header?>
        </h3>
    <? endif;?>
    <fieldset>
        <legend><?=_('Begründung:')?></legend>
        <label for="reason_text">
             <textarea name="reason_text" id="reason_text" cols="35" rows="4"><?=$reason_text?></textarea>
        </label>
    </fieldset>
    <?= Button::create(_('Sperren'),'store') ?>
    <?= LinkButton::create(_('Abbrechen'), $controller->url_for('document/administration/filter')) ?>
</form>

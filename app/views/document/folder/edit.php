<form action="<?= $controller->url_for('document/folder/edit/' . $folder_id) ?>" method="post" class="studip_form">
   <?= CSRFProtection::tokenTag() ?>

   <fieldset>
       <fieldset class="required">
           <label for="name"><?= _('Name:') ?></label>
           <input type="text" name="name" placeholder="<?= _('Ordnername') ?>" value="<?= htmlReady($folder->getName()) ?>" required>
       </fieldset>

       <fieldset>
           <label for="description"><?= _('Beschreibung:') ?></label>
           <textarea name="description" placeholder="<?= _('Optionale Beschreibung für den Ordner') ?>"><?= htmlReady($folder->description) ?></textarea>
       </fieldset>
   </fieldset>

   <?= Studip\Button::createAccept(_('Speichern')) ?>
   <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('document/files/list/' . $parent_id)) ?>
</form>

<form action="<?= $controller->url_for('document/folder/edit/' . $folder_id) ?>" method="post" class="studip_form">
   <?= CSRFProtection::tokenTag() ?>

   <fieldset>
       <fieldset class="required">
           <label>
               <?= _('Name:') ?>
               <input type="text" name="name" placeholder="<?= _('Ordnername') ?>" value="<?= htmlReady($folder->getName()) ?>" required>
           </label>
       </fieldset>

       <fieldset>
            <label>
                <?= _('Beschreibung:') ?>
                <textarea name="description" placeholder="<?= _('Optionale Beschreibung für den Ordner') ?>"><?= htmlReady($folder->description) ?></textarea>
            </label>
       </fieldset>
   </fieldset>

   <?= Studip\Button::createAccept(_('Speichern')) ?>
   <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('document/files/index/' . $parent_id)) ?>
</form>

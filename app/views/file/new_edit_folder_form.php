<?= CSRFProtection::tokenTag() ?>
<input type="hidden" name="form_sent" value="1">
<fieldset>
    <legend>
        <?= _("Ordnereigenschaften") ?>
    </legend>
    <label>
        <?= _('Name') ?>
        <input type="text" name="name" placeholder="<?= _('Name') ?>" value="<?= htmlReady($name); ?>">
    </label>
    <label>
        <?= _('Beschreibung') ?>
        <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
    </label>
</fieldset>


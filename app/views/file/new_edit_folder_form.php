<?= CSRFProtection::tokenTag() ?>
<input type="hidden" name="form_sent" value="1">
<fieldset>
    <label>
        <?= _('Name') ?>
        <input type="text" name="name" placeholder="<?= _('Name') ?>" value="<?= htmlReady($name); ?>">
    </label>
</fieldset>

<fieldset>
    <label>
        <?= _('Beschreibung') ?>
        <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
    </label>
</fieldset>

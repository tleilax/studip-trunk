<form class="default" method="post"
action="<?= URLHelper::getLink(
    'dispatch.php/admin/content_terms_of_use/'
        . (($add_action) ? 'add' : 'edit' )
    ) ?>">
    <?= CSRFProtection::tokenTag() ?>
<fieldset>
    <legend><?= _('Grunddaten') ?></legend>
    <? if($add_action): ?>
    <label>
        <?= _('ID') ?>
        <input type="text" name="entry_id" value="<?= htmlReady($entry_id) ?>">
    </label>
    <? else: ?>
    <input type="hidden" name="entry_id" value="<?= htmlReady($entry_id) ?>">
    <? endif ?>
    <label>
        <?= _('Name') ?>
        <?= I18N::input('entry_name', $entry_name)?>
    </label>
    <label>
        <?= _('Downloadbedingung') ?>
        <select name="entry_download_condition">
            <option value="0"
                <?= ($entry_download_condition == '0') ? 'selected="selected"' : '' ?>>
                <?= _('Ohne Bedingung') ?>
            </option>
            <option value="0"
                <?= ($entry_download_condition == '1') ? 'selected="selected"' : '' ?>>
                <?= _('Nur für geschlossene Gruppen') ?>
            </option>
            <option value="0"
                <?= ($entry_download_condition == '2') ? 'selected="selected"' : '' ?>>
                <?= _('Nur für Eigentümer') ?>
            </option>
        </select>
    </label>
    <label>
        <?= _('Symbol-Name oder URL') ?>
        <input type="text" name="entry_icon" value="<?= htmlReady($entry_icon) ?>">
    </label>
</fieldset>
<fieldset>
    <legend><?= _('Zusätzliche Angaben') ?></legend>
    <label>
        <?= _('Position') ?>
        <input type="number" name="entry_position" value="<?= htmlReady($entry_position) ?>">
    </label>
    <label>
        <?= _('Beschreibung') ?>
        <?= I18N::textarea('entry_description', $entry_description)?>
    </label>
</fieldset>
<div data-dialog-button>
    <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    <?= Studip\LinkButton::createCancel(_('Abbrechen'),
        URLHelper::getUrl('dispatch.php/admin/content_terms_of_use/index')
        ) ?>
</div>
</form>

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
        <input type="text" name="entry_id" value="<?= $entry_id ?>">
    </label>
    <? else: ?>
    <input type="hidden" name="entry_id" value="<?= $entry_id ?>">
    <? endif ?>
    <label>
        <?= _('Name') ?>
        <input type="text" name="entry_name" value="<?= $entry_name ?>">
    </label>
    <label>
        <?= _('Downloadbedingung') ?>
        <select name="entry_download_condition" value="<?= $entry_download_condition ?>">
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
        <input type="text" name="entry_icon" value="<?= $entry_icon ?>">
    </label>
</fieldset>
<fieldset>
    <legend><?= _('Zusätzliche Angaben') ?></legend>
    <label>
        <?= _('Position') ?>
        <input type="number" name="entry_position" value="<?= $entry_position ?>">
    </label>
    <label>
        <?= _('Beschreibung') ?>
        <input type="text" name="entry_description" value="<?= $entry_description ?>">
    </label>
</fieldset>
<div data-dialog-button>
    <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
    <?= Studip\LinkButton::createCancel(_('Abbrechen'),
        URLHelper::getUrl('dispatch.php/admin/content_terms_of_use/index')
        ) ?>
</div>
</form>

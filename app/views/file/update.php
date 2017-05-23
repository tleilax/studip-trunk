<form action="<?= URLHelper::getLink('dispatch.php/file/update/' . $file_ref->id) ?>"
    enctype="multipart/form-data" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Neue Dateiversion') ?></legend>
        <label class="file-upload">
            <?= sprintf(
                _('Bitte die neue Version der Datei %s auswählen.'),
                $file_ref->name
                ) ?>
            <input type="file" name="file">
        </label>
        <label>
            <input type="checkbox" name="update_filename" value="1">
            <?= _('Dateinamen aus neuer Dateiversion übernehmen.') ?>
        </label>
        <? $count = count($file_ref->file->refs) ?>
        <? if ($count > 1) : ?>
            <label>
                <input type="checkbox" name="update_all_instances" value="1">
                <?= sprintf(_('Alle weiteren %s Vorkommen aktualisieren.'), $count - 1) ?>
            </label>
        <? endif ?>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Aktualisieren'), 'confirm') ?>
        <? if (!Request::isDialog()) : ?>
            <?= \Studip\LinkButton::create(_("Abbrechen"), $controller->url_for(($folder->range_type === "course" ? "course/" : ($folder->range_type === "institute" ? "institute/" : "")).'files/index/' . $folder->parent_id)) ?>
        <? endif ?>
    </div>
</form>

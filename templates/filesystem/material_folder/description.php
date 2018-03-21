<div><?= htmlReady($type) ?>:</div>
<div>
    <div><?= _('Ein Ordner für Materialien, welche nur zum Download zu Verfügung gestellt werden sollen.') ?></div>
    <div>
        <?= _('Den Inhalt des Ordners können nur Lehrende und TutorInnen verändern.') ?>
        <?= _('Die normalen Teilnehmenden der Veranstaltung können diese Materialien nur herunterladen.') ?>
    </div>
    <? if ($folderdata['description']) : ?>
        <div>
            <?= htmlReady($folderdata['description']) ?>
        </div>
    <? endif ?>
</div>

<h1>
    <?= sprintf(
        _('Soll der Eintrag mit der ID "%s" wirklich gel�scht werden?'),
        $entry_id
    ) ?>
</h1>

<form class="default" method="post" action="<?= $controller->link_for('admin/content_terms_of_use/delete') ?>">
    <input type="hidden" name="entry_id" value="<?= htmlReady($entry_id) ?>">
<? if ($dependent_files_count): ?>
    <p>
        <?= sprintf(
            _('Bevor ein Eintrag gel�scht werden kann, m�ssen Dateien, welche auf ihn verweisen, einem anderen Eintrag zugewiesen werden! Es m�ssen %u Dateien bearbeitet werden!'),
            $dependent_files_count
        ) ?>
    </p>
    <label>
        <?= _('Name des anderen Eintrags:') ?>
        <select name="other_entry_id">
        <? foreach ($other_entries as $other_entry): ?>
            <option value="<?= htmlReady($other_entry->id) ?>" <? if ($other_entry_id === $other_entry->id) echo 'selected'; ?>>
                <?= htmlReady($other_entry->name) ?>
            </option>
        <? endforeach; ?>
        </select>
    </label>
<? else: ?>
    <p><?= _('Der Eintrag wird von keiner Datei benutzt!') ?></p>
<? endif; ?>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('L�schen'), 'confirm') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            URLHelper::getUrl('dispatch.php/admin/content_terms_of_use/index')
            ) ?>
    </div>
</form>

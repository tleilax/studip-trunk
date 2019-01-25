<form class="default" action="<?= $controller->url_for('admin/ilias_interface/save_interface_settings/') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <label>
        <span class="required"><?= _('Name des Moduls "ILIAS-Schnittstelle" in Veranstaltungen') ?></span>
        <input type="text" name="ilias_interface_moduletitle" size="50" maxlength="255" value="<?= htmlReady($ilias_interface_moduletitle) ?>" required>
    </label>
    <label>
        <input type="checkbox" name="ilias_interface_edit_moduletitle" value="1" <?= $ilias_interface_config['edit_moduletitle'] ? 'checked' : '' ?>>
        <span><?= _('Lehrende können den Seitennamen der ILIAS-Schnittstelle in Veranstaltungen anpassen') ?></span>
    </label>
    <label>
        <input type="checkbox" name="ilias_interface_show_offline" value="1" <?= $ilias_interface_config['show_offline'] ? 'checked' : '' ?>>
        <span><?= _('Namen von Lernobjekten und Kursen, die in ILIAS offline sind, sind in Stud.IP sichtbar') ?></span>
    </label>
    <label>
        <input type="checkbox" name="ilias_interface_add_statusgroups" value="1" <?= $ilias_interface_config['add_statusgroups'] ? 'checked' : '' ?>>
        <span><?= _('Lehrende können Statusgruppen nach ILIAS übertragen') ?></span>
    </label>
    <label>
        <input type="checkbox" name="ilias_interface_search_active" value="1" <?= $ilias_interface_config['search_active'] ? 'checked' : '' ?>>
        <span><?= _('Suche nach Lernobjekten verfügbar') ?></span>
    </label>
    <label>
        <input type="checkbox" name="ilias_interface_cache" value="1" <?= $ilias_interface_config['cache'] ? 'checked' : '' ?>>
        <span><?= _('SOAP-Cache') ?></span>
    </label>
    <? if (count($existing_indices)) : ?>
    <label>
        <span class="required"><?= _('Art der Verknüpfung') ?></span>
        <select name="ilias_index">
        <option value="new" selected><?=_('Neue Verknüpfung')?></option>
        <? foreach ($existing_indices as $existing_index => $data) : ?>
            <option value="<?=$existing_index?>"><?=sprintf(_('ILIAS-Installation (Index %s) aus vorheriger Verknüpfung'), $existing_index)?></option>
        <? endforeach ?>
        </select>
    </label>
    <? endif ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
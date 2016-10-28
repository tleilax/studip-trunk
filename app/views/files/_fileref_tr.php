<? $mime_type = File::find($file_ref->file_id)->mime_type; ?>
<tr <? if ($full_access) printf('data-file="%s"', $file_ref->id) ?>>
    <td>
        <input type="checkbox" name="ids[]" value="<?= $file_ref->id ?>" <? if (in_array($file_ref->id, $marked_element_ids)) echo 'checked'; ?>>
    </td>
    <td class="document-icon" data-sort-value="1">
        <a href="<?= $file_ref->getDownloadURL() ?>">
            <?= Icon::create(get_icon_for_mimetype($mime_type), 'clickable')->asImg(24) ?>
        </a>
    </td>
    <td>
        <a href="<?= $file_ref->getDownloadURL() ?>">
            <?= htmlReady($file_ref->name) ?>
        </a>
        <? if ($file_ref_file_restricted): ?>
            <?= Icon::create('lock-locked', 'clickable',['title' => _('Diese Datei ist nicht frei von Rechten Dritter.')])->asImg(['class' => 'text-top']) ?>
        <? endif; ?>
        <? if ($file_ref->description): ?>
            <small class="responsive-hidden"><?= htmlReady($file_ref->description) ?></small>
        <? endif; ?>
    </td>
    <td title="<?= number_format($file_ref->file->size, 0, ',', '.') . ' Byte' ?>" data-sort-value="<?= $file_ref->file->size ?>" class="responsive-hidden">
        <?= relSize($file_ref->file->size, false) ?>
    </td>
    <td data-sort-value="<?= $file_ref->owner->getFullName('no_title') ?>" class="responsive-hidden">
        <? if ($file_ref->owner->id !== $GLOBALS['user']->id): ?>
            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $file_ref->owner->username) ?>">
                <?= htmlReady($file_ref->owner->getFullName()) ?>
            </a>
        <? else: ?>
            <?= htmlReady($file_ref->owner->getFullName()) ?>
        <? endif; ?>
    </td>
    <td title="<?= strftime('%x %X', $file_ref->mkdate) ?>" data-sort-value="<?= $file_ref->mkdate ?>" class="responsive-hidden">
        <?= reltime($file_ref->mkdate) ?>
    </td>
    <td class="actions">
        <? $actionMenu = ActionMenu::get() ?>
        <? if ($file_ref->isEditable($GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/edit/' . $file_ref->id),
                _('Datei bearbeiten'),
                Icon::create('edit', 'clickable'),
                ['data-dialog' => 'size=auto; reload-on-close', '' => '']) ?>
        <? endif; ?>
        <? if ($file_ref->isDownloadable($GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($downloadlink,
                _('Datei herunterladen'),
                Icon::create('download', 'clickable')) ?>
        <? endif; ?>
        <? if ($file_ref->isDeletable($GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/move/' . $file_ref->id),
                _('Datei verschieben'),
                Icon::create('file+move_right', 'clickable'),
                ['data-dialog' => 'size=big; reload-on-close']) ?>
        <? endif; ?>
        <? if ($file_ref->isDownloadable($GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/copy/' . $file_ref->id),
                _('Datei kopieren'),
                Icon::create('file+add', 'clickable'),
                ['data-dialog' => 'size=auto; reload-on-close']) ?>
        <? endif; ?>
        <? if ($file_ref->isDeletable($GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/delete/' . $file_ref->id),
                _('Datei löschen'),
                Icon::create('trash', 'clickable'),
                ['data-confirm' => sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), htmlReady($file_ref->name)),
                    'data-dialog' => 'size=auto',
                    'formaction' => $controller->url_for('file/delete/' . $file_ref->id)]) ?>
        <? endif; ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>

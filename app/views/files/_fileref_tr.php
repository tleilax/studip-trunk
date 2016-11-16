<tr <? if ($full_access) printf('data-file="%s"', $file_ref->id) ?> id="fileref_<?= htmlReady($file_ref->id) ?>">
    <td>
        <input type="checkbox"
               name="ids[]"
               value="<?= $file_ref->id ?>"
               <? if (in_array($file_ref->id, (array) $marked_element_ids)) echo 'checked'; ?>>
    </td>
    <td class="document-icon" data-sort-value="1">
        <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)) : ?>
        <a href="<?= $file_ref->download_url ?>">
            <?= Icon::create(get_icon_for_mimetype($file_ref->mime_type), 'clickable')->asImg(24) ?>
        </a>
        <? else : ?>
            <?= Icon::create(get_icon_for_mimetype($file_ref->mime_type), "inactive")->asImg(24) ?>
        <? endif ?>
    </td>
    <td data-sort-value="<?= htmlReady($file_ref->name) ?>">
        <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)) : ?>
        <a href="<?= htmlReady($file_ref->download_url) ?>">
            <?= htmlReady($file_ref->name) ?>
        </a>
        <? else : ?>
            <?= htmlReady($file_ref->name) ?>
        <? endif ?>
        <? if ($file_ref->terms_of_use): ?>
            <? if($file_ref->terms_of_use->download_condition > 1): ?>
                <?= Icon::create('lock-locked', 'info')->asImg(['class' => 'text-top', 'title' => _('Diese Datei ist nicht frei von Rechten Dritter.')]) ?>
            <? endif ?>
        <? endif; ?>
        <? if ($file_ref->description): ?>
            <small class="responsive-hidden"><?= htmlReady($file_ref->description) ?></small>
        <? endif; ?>
    </td>
    <td title="<?= number_format($file_ref->size, 0, ',', '.') . ' Byte' ?>" data-sort-value="<?= $file_ref->size ?>" class="responsive-hidden">
        <?= relSize($file_ref->size, false) ?>
    </td>
    <td data-sort-value="<?= htmlReady($file_ref->author_name) ?>" class="responsive-hidden">
        <? if ($file_ref->user_id !== $GLOBALS['user']->id && $file_ref->owner): ?>
            <a href="<?= URLHelper::getScriptLink('dispatch.php/profile?username=' . $file_ref->owner->username) ?>">
                <?= htmlReady($file_ref->author_name) ?>
            </a>
        <? else: ?>
            <?= htmlReady($file_ref->author_name) ?>
        <? endif; ?>
    </td>
    <td title="<?= strftime('%x %X', $file_ref->mkdate) ?>" data-sort-value="<?= $file_ref->mkdate ?>" class="responsive-hidden">
        <?= reltime($file_ref->mkdate) ?>
    </td>
    <td class="actions">
        <? $actionMenu = ActionMenu::get() ?>
        
        <? if (Navigation::hasItem('/course/files_new/flat') && Navigation::getItem('/course/files_new/flat')->isActive()) : ?>
         <? $actionMenu->addLink($controller->url_for('course/files/index/' . $file_ref->folder_id),
                _('Ordner öffnen'), Icon::create('folder-empty', 'clickable')) ?>         
         <? elseif (Navigation::hasItem('/profile/files/flat') && Navigation::getItem('/profile/files/flat')->isActive()) : ?>
             <? $actionMenu->addLink($controller->url_for('files/index/' . $file_ref->folder_id),
                _('Ordner öffnen'), Icon::create('folder-empty', 'clickable')) ?>                
        <? endif; ?>        
        <? if ($current_folder->isFileEditable($file_ref->id, $GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/edit/' . $file_ref->id),
                _('Datei bearbeiten'),
                Icon::create('edit', 'clickable'),
                ['data-dialog' => 'reload-on-close', '' => '']) ?>
        <? endif; ?>
        <? if ($current_folder->isFileWritable($file_ref->id, $GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/move/' . $file_ref->id, array('copymode' => 'move')),
                _('Datei verschieben'),
                Icon::create('file+move_right', 'clickable'),
                ['data-dialog' => 'size=400']) ?>
        <? endif; ?>
        <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/move/' . $file_ref->id, array('copymode' => 'copy')),
                _('Datei kopieren'),
                Icon::create('file+add', 'clickable'),
                ['data-dialog' => 'size=400']) ?>
        <? endif; ?>
        <? if ($current_folder->isFileWritable($file_ref->id, $GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/delete/' . $file_ref->id),
                _('Datei löschen'),
                Icon::create('trash', 'clickable'),
                [
                    //'data-confirm' => sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), htmlReady($file_ref->name)),
                    //'data-dialog' => '1'//,
                    'onClick' => "STUDIP.Dialog.confirm('".sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), htmlReady($file_ref->name))."', function () { STUDIP.Files.removeFile('". $file_ref->id . "'); }); return false;"
                    //'formaction' => $controller->url_for('file/delete/' . $file_ref->id)
                ]) ?>
        <? endif; ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>

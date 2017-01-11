<tr class="<?= ($file_ref->chdate > $last_visitdate) ? 'new' : ''?>" <? if ($full_access) printf('data-file="%s"', $file_ref->id) ?> id="fileref_<?= htmlReady($file_ref->id) ?>">
    <td>
        <input type="checkbox"
               class="document-checkbox"
               name="ids[]"
               id="file_checkbox_<?=$file_ref->id?>"
               value="<?= $file_ref->id ?>"
               <? if (in_array($file_ref->id, (array) $marked_element_ids)) echo 'checked'; ?>>
        <label for="file_checkbox_<?=$file_ref->id?>"><span></span></label>
    </td>
    <td class="document-icon" data-sort-value="1">
        <a href="<?= $controller->url_for('file/details/' . $file_ref->id) ?>" data-dialog="1">
            <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)) : ?>
                <?= Icon::create(get_icon_for_mimetype($file_ref->mime_type), 'clickable')->asImg(24) ?>
            <? else : ?>
                <?= Icon::create(get_icon_for_mimetype($file_ref->mime_type), "inactive")->asImg(24) ?>
            <? endif ?>
        </a>
    </td>
    <td data-sort-value="<?= htmlReady($file_ref->name) ?>">
        <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)) : ?>
        <a href="<?= htmlReady($file_ref->download_url) ?>" target="_blank">
            <?= htmlReady($file_ref->name) ?>
        </a>
        <? else : ?>
            <?= htmlReady($file_ref->name) ?>
        <? endif ?>
        <? if ($file_ref->terms_of_use): ?>
            <? if($file_ref->terms_of_use->download_condition > 0): ?>
                <?= Icon::create('lock-locked', 'info')->asImg(['class' => 'text-top', 'title' => _('Das Herunterladen dieser Datei ist nur eingeschränkt möglich.')]) ?>
            <? endif ?>
        <? endif; ?>
    </td>
    <td title="<?= number_format($file_ref->size, 0, ',', '.') . ' Byte' ?>" data-sort-value="<?= $file_ref->size ?>" class="responsive-hidden">
        <? if ($file_ref->is_link) : ?>
            <?= _("Weblink") ?>
        <? else : ?>
            <?= relSize($file_ref->size, false) ?>
        <? endif ?>
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
    <td title="<?= strftime('%x %X', $file_ref->chdate) ?>" data-sort-value="<?= $file_ref->chdate ?>" class="responsive-hidden">
        <?= reltime($file_ref->chdate) ?>
    </td>
    <td class="actions">
        <? $actionMenu = ActionMenu::get() ?>

        <? if (Navigation::hasItem('/course/files_new/flat') && Navigation::getItem('/course/files_new/flat')->isActive()) : ?>
         <? $actionMenu->addLink($controller->url_for('course/files/index/' . $file_ref->folder_id),
                _('Ordner öffnen'), Icon::create('folder-empty', 'clickable', array('size' => 20))) ?>
         <? elseif (Navigation::hasItem('/profile/files/flat') && Navigation::getItem('/profile/files/flat')->isActive()) : ?>
             <? $actionMenu->addLink($controller->url_for('files/index/' . $file_ref->folder_id),
                _('Ordner öffnen'), Icon::create('folder-empty', 'clickable', array('size' => 20))) ?>
        <? endif; ?>
        <? if ($current_folder->isFileEditable($file_ref->id, $GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/edit/' . $file_ref->id),
                _('Datei bearbeiten'),
                Icon::create('edit', 'clickable', array('size' => 20)),
                ['data-dialog' => '1', '' => '']) ?>
        <? endif; ?>
        <? if ($current_folder->isFileWritable($file_ref->id, $GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/choose_destination/' . $file_ref->id, array('copymode' => 'move')),
                _('Datei verschieben'),
                Icon::create('file+move_right', 'clickable', array('size' => 20)),
                ['data-dialog' => 'size=auto']) ?>
        <? endif; ?>
        <? if ($current_folder->isFileDownloadable($file_ref, $GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/choose_destination/' . $file_ref->id, array('copymode' => 'copy')),
                _('Datei kopieren'),
                Icon::create('file+add', 'clickable', array('size' => 20)),
                ['data-dialog' => 'size=auto']) ?>
        <? endif; ?>
        <? if ($current_folder->isFileWritable($file_ref->id, $GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/delete/' . $file_ref->id),
                _('Datei löschen'),
                Icon::create('trash', 'clickable', array('size' => 20)),
                [
                    'onClick' => "return STUDIP.Dialog.confirmAsPost('" . sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), htmlReady($file_ref->name)) . "', this.href);"
                ]) ?>
        <? endif; ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>

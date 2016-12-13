<? $controllerpath = ($topFolder->range_type === "user" ? "" : $topFolder->range_type."/").'files/index' ?>
<? $is_readable = $folder->isReadable($GLOBALS['user']->id) ?>
<? $owner = User::find($folder->user_id) ?: new User() ?>
<tr id="row_folder_<?= $folder->id ?>">
    <td>
        <? if ($is_readable) : ?>
        <input type="checkbox"
                name="ids[]"
                class="document-checkbox"
                id="file_checkbox_<?=$folder->getId()?>"
                value="<?= $folder->getId() ?>"
                onchange="javascript:void(STUDIP.Files.toggleBulkButtons());"
                <?= (in_array($folder->getId(), $marked_element_ids)) ? 'checked' : '' ?>>
        <label for="file_checkbox_<?=$folder->getId()?>"><span></span></label>
        <? endif?>
    </td>
    <td class="document-icon" data-sort-value="0">
        <? if ($is_readable) : ?>
            <a href="<?= $controller->url_for($controllerpath . '/' . $folder->getId()) ?>">
        <? endif ?>
            <?= $folder->getIcon($is_readable ? 'clickable': 'info')->asImg(26) ?>
        <? if ($is_readable) : ?>
        </a>
        <? endif ?>
    </td>
    <td>
        <? if ($is_readable) : ?>
            <a href="<?= $controller->url_for($controllerpath . '/' . $folder->getId()) ?>">
        <? endif ?>
            <?= htmlReady($folder->name) ?>
        <? if ($is_readable) : ?>
            </a>
        <? endif ?>
    <? if ($folder->description): ?>
        <small class="responsive-hidden"><?= htmlReady($folder->description) ?></small>
    <? endif; ?>
    </td>
    <? // -number + file count => directories should be sorted apart from files ?>
    <td data-sort-value="<?= -1000000 ?>" class="responsive-hidden">
    </td>
    <td data-sort-value="<?= htmlReady($owner->getFullName('no_title')) ?>" class="responsive-hidden">
    <? if ($folder->user_id !== $GLOBALS['user']->id) : ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $owner->username) ?>">
            <?= htmlReady($owner->getFullName('no_title')) ?>
        </a>
    <? else: ?>
        <?= htmlReady($owner->getFullName('no_title')) ?>
    <? endif; ?>
    </td>
    <td title="<?= strftime('%x %X', $folder->mkdate) ?>" data-sort-value="<?= $folder->mkdate ?>" class="responsive-hidden">
        <?= reltime($folder->mkdate) ?>
    </td>
    <td class="actions">
        <? $actionMenu = ActionMenu::get() ?>
        <? if ($folder->isWritable($GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/edit_folder/' . $folder->getId()),
                    _('Ordner bearbeiten'),
                    Icon::create('edit', 'clickable', array('size' => 20)),
                    ['data-dialog' => '1']) ?>
        <? endif; ?>
        <? $actionMenu->addLink($downloadlink,
                _('Ordner herunterladen'),
                Icon::create('download', 'clickable', array('size' => 20))) ?>
        <? if ($folder->isWritable($GLOBALS['user']->id)): ?>
           <? $actionMenu->addLink($controller->url_for('file/choose_destination/' . $folder->getId(), array('copymode' => 'move', 'isfolder' => 1)),
                    _('Ordner verschieben'),
                    Icon::create('folder-empty+move_right', 'clickable', array('size' => 20)),
                    ['data-dialog' => '1']) ?>
            <? $actionMenu->addLink($controller->url_for('file/choose_destination/' . $folder->getId(), array('copymode' => 'copy', 'isfolder' => 1)),
                    _('Ordner kopieren'),
                    Icon::create('folder-empty+add', 'clickable', array('size' => 20)),
                    ['data-dialog' => '1']) ?>
            <? $actionMenu->addLink(
                    $controller->url_for('folder/delete/' . $folder->getId()),
                    _('Ordner löschen'),
                    Icon::create('trash', 'clickable', array('size' => 20)),
                    [
                        'onclick' => "STUDIP.Dialog.confirm('".sprintf(_('Soll der Ordner "%s" wirklich gelöscht werden?'), htmlReady($folder->name))."', function () { STUDIP.Folders.delete('". $folder->getId() . "'); }); return false;"
                    ]) ?>
        <? endif; ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>
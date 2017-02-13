<? if (!$controllerpath) : ?>
    <? $controllerpath = ($topFolder->range_type === "user" ? "" : $topFolder->range_type."/").'files/index' ?>
<? endif ?>
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
                <?= (in_array($folder->getId(), (array) $marked_element_ids)) ? 'checked' : '' ?>>
        <label for="file_checkbox_<?=$folder->getId()?>"><span></span></label>
        <? endif?>
    </td>
    <td class="document-icon" data-sort-value="0">
        <a href="<?= $controller->url_for('file/details/' . $folder->getId())  ?>" data-dialog>
            <?= $folder->getIcon($is_readable ? 'clickable': 'info')->asImg(26) ?>
        </a>
    </td>
    <td>
        <? if ($is_readable) : ?>
            <a href="<?= $controller->url_for($controllerpath . '/' . $folder->getId()) ?>">
        <? endif ?>
            <?= htmlReady($folder->name) ?>
        <? if ($is_readable) : ?>
            </a>
        <? endif ?>
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
        <? if ($folder->isEditable($GLOBALS['user']->id)): ?>
            <? $actionMenu->addLink($controller->url_for('file/edit_folder/' . $folder->getId()),
                    _('Ordner bearbeiten'),
                    Icon::create('edit', 'clickable', array('size' => 20)),
                    ['data-dialog' => '1']) ?>
        <? endif; ?>
        <? $actionMenu->addLink($downloadlink,
                _('Ordner herunterladen'),
                Icon::create('download', 'clickable', array('size' => 20))) ?>
        <? if ($folder->isEditable($GLOBALS['user']->id)): ?>
           <? $actionMenu->addLink($controller->url_for('file/choose_destination/' . $folder->getId(), array('copymode' => 'move', 'isfolder' => 1)),
                    _('Ordner verschieben'),
                    Icon::create('folder-empty+move_right', 'clickable', array('size' => 20)),
                    ['data-dialog' => 'size=auto']) ?>
            <? $actionMenu->addLink($controller->url_for('file/choose_destination/' . $folder->getId(), array('copymode' => 'copy', 'isfolder' => 1)),
                    _('Ordner kopieren'),
                    Icon::create('folder-empty+add', 'clickable', array('size' => 20)),
                    ['data-dialog' => 'size=auto']) ?>
            <? $actionMenu->addLink(
                    $controller->url_for('file/delete_folder/' . $folder->getId()),
                    _('Ordner l�schen'),
                    Icon::create('trash', 'clickable', array('size' => 20)),
                    [
                        'onclick' => "return STUDIP.Dialog.confirmAsPost('".sprintf(_('Soll der Ordner "%s" wirklich gel�scht werden?'), htmlReady($folder->name))."', this.href);"
                    ]) ?>
        <? endif; ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>

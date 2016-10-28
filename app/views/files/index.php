<form method="post">
<table class="default" data-folder_id="<?= htmlReady($topFolder->getId()) ?>">
    <caption>
        <div class="caption-container">
            <? $full_access = true;
               $breadcrumbs = $topFolder->getParents();
               $last_crumb = end($topFolder->getParents()); ?>
        <? if (count($breadcrumbs) > 1) : ?>
            <div class="extendable bread-crumbs" title="<?= _('In übergeordnete Verzeichnisse wechseln') ?>">
        <? else: ?>
            <div class="bread-crumbs">
        <? endif; ?>
            <a href="<?= $controller->link_for('/index/' . $last_crumb->id) ?>">
                <?= Icon::create('folder-parent', 'clickable')->asImg(24) ?>
            </a>
            <? if (count($breadcrumbs) > 1) : ?>
                <ul>
                <? foreach ($breadcrumbs as $crumb) : ?>
                    <li>
                        <a href="<?= $controller->url_for('/index/' . $crumb->id) ?>">
                            <?= htmlReady($crumb->name) ?>
                        </a>
                    </li>
                <? endforeach; ?>
                </ul>
            <? endif; ?>
            </div>
            <div class="caption-content">
                <header class="folder-description">
                    <h2>
                        <?= htmlReady($last_crumb->name) ?>
                    </h2>
                <? if ($last_crumb->description) : ?>
                    <p><?= formatReady($last_crumb['description']) ?></p>
                <? endif; ?>
                </header>
            </div>
        </div>
    </caption>
    <colgroup>
        <col width="30px">
        <col width="20px">
        <col>
        <col width="100px" class="responsive-hidden">
        <col width="150px" class="responsive-hidden">
        <col width="120px" class="responsive-hidden">
        <col width="121px">
    </colgroup>
    <thead>
        <tr class="sortable">
            <th data-sort="false">
                <input type="checkbox" data-proxyfor=":checkbox[name='ids[]']"
                       data-activates="table.documents tfoot button">
            </th>
            <th data-sort="htmldata"><?= _('Typ') ?></th>
            <th data-sort="text"><?= _('Name') ?></th>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Größe') ?></th>
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Autor/in') ?></th>
            <th data-sort="htmldata" class="responsive-hidden sortasc"><?= _('Datum') ?></th>
            <th data-sort="false"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
<? if (!$isRoot) : ?>
    <tbody>
        <tr class="chdir-up" <? if ($full_access) printf('data-folder="%s"', $folder_id) ?> data-sort-fixed>
            <td>&nbsp;</td>
            <td class="document-icon">
                <a href="<?= $controller->url_for('/index/' . $parent_id, $parent_page ) ?>">
                    <?//= Icon::create('arr_1up', 'clickable', ['title' => _('Ein Verzeichnis nach oben wechseln')])->asImg(24) ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->url_for('/index/' . $parent_id, $parent_page) ?>" title="<?= _('Ein Verzeichnis nach oben wechseln') ?>">
                    <small><?= _('Ein Verzeichnis nach oben wechseln') ?></small>
                </a>
            </td>
            <td class="responsive-hidden">&nbsp;</td>
            <td class="responsive-hidden">&nbsp;</td>
            <td class="responsive-hidden">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </tbody>
<? endif; ?>
<? if (count($topFolder->subfolders) + count($topFolder->file_refs) === 0): ?>
    <tbody>
        <tr>
            <td colspan="8" class="empty">
                <?= _('Dieser Ordner ist leer') ?>
            </td>
        </tr>
    </tbody>
<? elseif (count($topFolder->subfolders)) : ?>
    <tbody>
    <? foreach ($topFolder->subfolders as $file) : ?>
        <tr <? if ($full_access) printf('data-file="%s"', $file->id) ?> <? if ($full_access) printf('data-folder="%s"', $file->id); ?>>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $file->id ?>" <? if (in_array($file->id, $markedElementIds)) echo 'checked'; ?>>
            </td>
            <td class="document-icon" data-sort-value="0">
                <a href="<?= $controller->url_for('document/files/index/' . $file->id) ?>">
                <? if ($is_empty): ?>
                    <?= Icon::create('folder-empty', 'clickable')->asImg(24) ?>
                <? else: ?>
                    <?= Icon::create('folder-full', 'clickable')->asImg(24) ?>
                <? endif; ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->url_for('/index/' . $file->id) ?>">
                    <?= htmlReady($file->name) ?>
                </a>
            <? if ($file->description): ?>
                <small class="responsive-hidden"><?= htmlReady($file->description) ?></small>
            <? endif; ?>
            </td>
            <? // -number + file count => directories should be sorted apart from files ?>
            <td data-sort-value="<?= -1000000 ?>" class="responsive-hidden">
            </td>
            <td data-sort-value="<?= htmlReady($file->owner->getFullName('no_title')) ?>" class="responsive-hidden">
            <? if ($file->owner->id !== $GLOBALS['user']->id) : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $file->owner->username) ?>">
                    <?= htmlReady($file->owner->getFullName()) ?>
                </a>
            <? else: ?>
                <?= htmlReady($file->owner->getFullName()) ?>
            <? endif; ?>
            </td>
            <td title="<?= strftime('%x %X', $file->mkdate) ?>" data-sort-value="<?= $file->mkdate ?>" class="responsive-hidden">
                <?= reltime($file->mkdate) ?>
            </td>
            <td class="actions">
                <? $actionMenu = ActionMenu::get() ?>
                <? if ($full_access): ?>
                    <? $actionMenu->addLink($controller->url_for('folder/edit/' . $file->id),
                            _('Ordner bearbeiten'),
                            Icon::create('edit', 'clickable'),
                            ['data-dialog' => 'size=auto']) ?>
                <? endif; ?>
                <? $actionMenu->addLink($downloadlink,
                        _('Ordner herunterladen'),
                        Icon::create('download', 'clickable')) ?>
                <? if ($full_access): ?>
                    <? $actionMenu->addLink($controller->url_for('folder/move/' . $file->id),
                            _('Ordner verschieben'),
                            Icon::create('folder-empty+move_right', 'clickable'),
                            ['data-dialog' => 'size=auto']) ?>
                    <? $actionMenu->addLink($controller->url_for('folder/copy/' . $file->id),
                            _('Ordner kopieren'),
                            Icon::create('folder-empty+add', 'clickable'),
                            ['data-dialog' => 'size=auto']) ?>
                    <? $actionMenu->addLink($controller->url_for('folder/delete/' . $file->id),
                            _('Ordner löschen'),
                            Icon::create('trash', 'clickable'),
                            ['data-confirm' => sprintf(_('Soll den Ordner "%s" wirklich gelöscht werden?'), htmlReady($file->name)),
                             'data-dialog' => 'size=auto',
                             'formaction' => $controller->url_for('folder/delete/' . $file->id)]) ?>
                <? endif; ?>
                <?= $actionMenu->render() ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
<? endif; ?>
<? if (count($topFolder->file_refs)) : ?>
    <tbody>
    <? foreach ($topFolder->file_refs as $file_ref) : ?>    
        <? $mime_type = File::find($file_ref->file_id)->mime_type; ?>    	
        <tr <? if ($full_access) printf('data-file="%s"', $file_ref->id) ?>>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $file->id ?>" <? if (in_array($file_ref->id, $markedElementIds)) echo 'checked'; ?>>
            </td>
            <td class="document-icon" data-sort-value="1">
                <a href="<?= $file_ref->getDownloadURL() ?>">
                    <?= Icon::create(get_icon_for_mimetype($mime_type), 'clickable')->asImg(24) ?>
                </a>
            </td>
            <td>
                <a href="<?= $file_ref->getDownloadURL() ?>">
                    <?= htmlReady($file_ref->file->name) ?>
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
            <td data-sort-value="<?= $file_ref->file->owner->getFullName('no_title') ?>" class="responsive-hidden">
            <? if ($file_ref->file->owner->id !== $GLOBALS['user']->id): ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $file_ref->file->owner->username) ?>">
                <?= htmlReady($file_ref->file->owner->getFullName()) ?>
                </a>
            <? else: ?>
                <?= htmlReady($file_ref->file->owner->getFullName()) ?>
            <? endif; ?>
            </td>
            <td title="<?= strftime('%x %X', $file_ref->file->mkdate) ?>" data-sort-value="<?= $file_ref->file->mkdate ?>" class="responsive-hidden">
                <?= reltime($file_ref->file->mkdate) ?>
            </td>
            <td class="actions">
                <? $actionMenu = ActionMenu::get() ?>
                <? if ($full_access): ?>
                    <? $actionMenu->addLink($controller->url_for('file/edit/' . $file_ref->id),
                            _('Datei bearbeiten'),
                            Icon::create('edit', 'clickable'),
                            ['data-dialog' => 'size=auto']) ?>
                <? endif; ?>
                <? $actionMenu->addLink($downloadlink,
                        _('Datei herunterladen'),
                        Icon::create('download', 'clickable')) ?>
                <? if ($full_access): ?>
                    <? $actionMenu->addLink($controller->url_for('file/move/' . $file_ref->id),
                            _('Datei verschieben'),
                            Icon::create('file+move_right', 'clickable'),
                            ['data-dialog' => 'size=auto']) ?>
                    <? $actionMenu->addLink($controller->url_for('file/copy/' . $file_ref->id),
                            _('Datei kopieren'),
                            Icon::create('file+add', 'clickable'),
                            ['data-dialog' => 'size=auto']) ?>
                    <? $actionMenu->addLink($controller->url_for('file/delete/' . $file_ref->id),
                            _('Datei löschen'),
                            Icon::create('trash', 'clickable'),
                            ['data-confirm' => sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), htmlReady($file_ref->file->name)),
                             'data-dialog' => 'size=auto',
                             'formaction' => $controller->url_for('file/delete/' . $file_ref->id)]) ?>
                <? endif; ?>
                <?= $actionMenu->render() ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endif; ?>
    <tfoot>
        <tr>
            <td colspan="100">
        <? if ($full_access || extension_loaded('zip')): ?>
            <? if (extension_loaded('zip')): ?>
                <?= Studip\Button::create(_('Herunterladen'), 'download') ?>
            <? endif; ?>
            <? if ($full_access): ?>
                <?= Studip\Button::create(_('Verschieben'), 'move', array('data-dialog' => '')) ?>
                <?= Studip\Button::create(_('Kopieren'), 'copy', array('data-dialog' => ''))?>
                <?= Studip\Button::create(_('Löschen'), 'delete') ?>
            <? endif; ?>
        <? endif; ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>

<form style="display: none;" id="file_selector">
    <input type="file" name="files[]" multiple onChange="STUDIP.Files.upload(this.files);">
</form>
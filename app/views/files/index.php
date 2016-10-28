<form method="post">
<table class="default documents" data-folder_id="<?= htmlReady($topFolder->getId()) ?>">
    <caption>
        <div class="caption-container">
            <? $full_access = true;
               $breadcrumbs = $topFolder->getParents();
               $last_crumb = end($topFolder->getParents()); ?>
        <? if (count($breadcrumbs) > 1) : ?>
            <div class="extendable bread-crumbs" title="<?= _('In �bergeordnete Verzeichnisse wechseln') ?>">
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
            <th data-sort="htmldata" class="responsive-hidden"><?= _('Gr��e') ?></th>
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
    <? foreach ($topFolder->subfolders as $folder) : ?>
        <tr <? if ($full_access) printf('data-file="%s"', $folder->id) ?> <? if ($full_access) printf('data-folder="%s"', $folder->id); ?>>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $file->id ?>" <? if (in_array($file->id, $marked_element_ids)) echo 'checked'; ?>>
            </td>
            <td class="document-icon" data-sort-value="0">
                <a href="<?= $controller->url_for('document/files/index/' . $folder->id) ?>">
                <? if ($is_empty): ?>
                    <?= Icon::create('folder-empty', 'clickable')->asImg(24) ?>
                <? else: ?>
                    <?= Icon::create('folder-full', 'clickable')->asImg(24) ?>
                <? endif; ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->url_for('/index/' . $folder->id) ?>">
                    <?= htmlReady($folder->name) ?>
                </a>
            <? if ($folder->description): ?>
                <small class="responsive-hidden"><?= htmlReady($folder->description) ?></small>
            <? endif; ?>
            </td>
            <? // -number + file count => directories should be sorted apart from files ?>
            <td data-sort-value="<?= -1000000 ?>" class="responsive-hidden">
            </td>
            <td data-sort-value="<?= htmlReady($folder->owner->getFullName('no_title')) ?>" class="responsive-hidden">
            <? if ($folder->owner->id !== $GLOBALS['user']->id) : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $folder->owner->username) ?>">
                    <?= htmlReady($folder->owner->getFullName()) ?>
                </a>
            <? else: ?>
                <?= htmlReady($folder->owner->getFullName()) ?>
            <? endif; ?>
            </td>
            <td title="<?= strftime('%x %X', $folder->mkdate) ?>" data-sort-value="<?= $folder->mkdate ?>" class="responsive-hidden">
                <?= reltime($folder->mkdate) ?>
            </td>
            <td class="actions">
                <? $actionMenu = ActionMenu::get() ?>
                <? if ($full_access): ?>
                    <? $actionMenu->addLink($controller->url_for('folder/edit/' . $folder->id),
                            _('Ordner bearbeiten'),
                            Icon::create('edit', 'clickable'),
                            ['data-dialog' => 'size=auto; reload-on-close']) ?>
                <? endif; ?>
                <? $actionMenu->addLink($downloadlink,
                        _('Ordner herunterladen'),
                        Icon::create('download', 'clickable')) ?>
                <? if ($full_access): ?>
                    <? $actionMenu->addLink($controller->url_for('folder/move/' . $folder->id),
                            _('Ordner verschieben'),
                            Icon::create('folder-empty+move_right', 'clickable'),
                            ['data-dialog' => 'size=auto; reload-on-close']) ?>
                    <? $actionMenu->addLink($controller->url_for('folder/copy/' . $folder->id),
                            _('Ordner kopieren'),
                            Icon::create('folder-empty+add', 'clickable'),
                            ['data-dialog' => 'size=auto; reload-on-close']) ?>
                    <? $actionMenu->addLink($controller->url_for('folder/delete/' . $folder->id),
                            _('Ordner l�schen'),
                            Icon::create('trash', 'clickable'),
                            ['data-confirm' => sprintf(_('Soll den Ordner "%s" wirklich gel�scht werden?'), htmlReady($folder->name)),
                             'data-dialog' => 'size=auto; reload-on-close',
                             'formaction' => $controller->url_for('folder/delete/' . $folder->id)]) ?>
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
        <?= $this->render_partial("files/_fileref_tr", compact("controller", "file_ref", "file_ref_file_restricted")) ?>
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
                <?= Studip\Button::create(_('L�schen'), 'delete') ?>
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

<div class="file_uploader">
    <ul class="filenames clean"></ul>
    <div class="uploadbar">
        <?= Icon::create("upload", "info_alt")->asImg(30) ?>
    </div>
</div>

<div class="source_selector">
    <?= _("Quelle ausw�hlen") ?>
    <div class="file_select_possibilities">
        <a href="#" onClick="jQuery('#file_selector input[type=file]').click(); return false;">
            <?= Icon::create("computer", "clickable")->asImg(50) ?>
            <?= _("Mein Computer") ?>
        </a>
        <a href="">
            <?= Icon::create("files", "clickable")->asImg(50) ?>
            <?= _("Meine Dateien") ?>
        </a>
        <a href="">
            <?= Icon::create("computer", "clickable")->asImg(50) ?>
            <?= _("OwnCloud") ?>
        </a>
        <a href="">
            <?= Icon::create("service", "clickable")->asImg(50) ?>
            <?= _("Lernmaterialien") ?>
        </a>
    </div>
</div>
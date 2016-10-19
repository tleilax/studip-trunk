<table class="default documents <? if (count($topFolder->file_refs) > 0) : ?>sortable-table<? endif ?>">
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
        <col width="25px">
        <col width="30px">
        <col width="20px">
        <col>
        <col width="100px">
        <col width="150px">
        <col width="120px">
        <col width="120px">
    </colgroup>
    <thead>
        <tr>
            <th data-sort="false">&nbsp;</th>
            <th data-sort="false">
                <input type="checkbox" data-proxyfor=":checkbox[name='ids[]']"
                       data-activates="table.documents tfoot button">
            </th>
            <th data-sort="htmldata"><?= _('Typ') ?></th>
            <th data-sort="text"><?= _('Name') ?></th>
            <th data-sort="htmldata"><?= _('Größe') ?></th>
            <th data-sort="htmldata"><?= _('Autor/in') ?></th>
            <th data-sort="htmldata"><?= _('Datum') ?></th>
            <th data-sort="false"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
<? if (!$isRoot) : ?>
        <tr class="chdir-up" <? if ($full_access) printf('data-folder="%s"', $folder_id) ?> data-sort-fixed>
            <td>&nbsp;</td>
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
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
<? endif; ?>
<? if (count($topFolder->subfolders) + count($topFolder->file_refs) === 0): ?>
        <tr>
            <td colspan="8" class="empty">
                <?= _('Dieser Ordner ist leer') ?>
            </td>
        </tr>
<? else: ?>
    <? foreach ($topFolder->subfolders as $file) : ?>
        <tr <? if ($full_access) printf('data-file="%s"', $file->id) ?> <? if ($full_access) printf('data-folder="%s"', $file->id); ?>>
            <td>
            </td>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $file->id ?>" <? if (in_array($file->id, $marked)) echo 'checked'; ?>>
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
                <small><?= htmlReady($file->description) ?></small>
            <? endif; ?>
            </td>
            <? // -number + file count => directories should be sorted apart from files ?>
            <td data-sort-value="<?= -1000000 ?>">
            </td>
            <td data-sort-value="<?= htmlReady($file->owner->getFullName('no_title')) ?>">
            <? if ($file->owner->id !== $GLOBALS['user']->id) : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $file->owner->username) ?>">
                    <?= htmlReady($file->owner->getFullName()) ?>
                </a>
            <? else: ?>
                <?= htmlReady($file->owner->getFullName()) ?>
            <? endif; ?>
            </td>
            <td title="<?= strftime('%x %X', $file->mkdate) ?>" data-sort-value="<?= $file->mkdate ?>">
                <?= reltime($file->mkdate) ?>
            </td>
            <td class="options">
            <? if ($full_access): ?>
                <a href="<?= $controller->url_for('folder/edit/' . $file->id) ?>" data-dialog="size=auto" title="<?= _('Ordner bearbeiten') ?>">
                    <?= Icon::create('edit', 'clickable')->asImg(16, ["alt" => _('bearbeiten')]) ?>
                </a>
            <? endif; ?>
                <a href="<?= $downloadlink ?>" title="<?= _('Ordner herunterladen') ?>">
                    <?= Icon::create('download', 'clickable')->asImg(16, ["alt" => _('herunterladen')]) ?>
                </a>
            <? if ($full_access): ?>
                <a href="<?= $controller->url_for('folder/move/' . $file->id) ?>" data-dialog="size=auto" title="<?= _('Ordner verschieben') ?>">
                    <?= Icon::create('folder-empty+move_right', 'clickable')->asImg(16, ["alt" => _('verschieben')]) ?>
                </a>
                 <a href="<?= $controller->url_for('folder/copy/' . $file->id) ?>" data-dialog="size=auto" title="<?= _('Ordner kopieren') ?>">
                    <?= Icon::create('folder-empty+add', 'clickable')->asImg(16, ["alt" => _('kopieren')]) ?>
                </a>
                <a href="<?= $controller->url_for('folder/delete/' . $file->id) ?>" title="<?= _('Ordner löschen') ?>">
                    <?= Icon::create('trash', 'clickable')->asImg(16, ["alt" => _('löschen')]) ?>
                </a>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach ?>
    <? foreach ($topFolder->file_refs as $file_ref) : ?>
        <tr <? if ($full_access) printf('data-file="%s"', $file_ref->id) ?>>
            <td>
            </td>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $file->id ?>" <? if (in_array($file_ref->id, $marked)) echo 'checked'; ?>>
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
                <small><?= htmlReady($file_ref->description) ?></small>
            <? endif; ?>
            </td>
            <td title="<?= number_format($file_ref->file->size, 0, ',', '.') . ' Byte' ?>" data-sort-value="<?= $file_ref->file->size ?>">
                <?= relSize($file_ref->file->size, false) ?>
            </td>
            <td data-sort-value="<?= $file_ref->file->owner->getFullName('no_title') ?>">
            <? if ($file_ref->file->owner->id !== $GLOBALS['user']->id): ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $file_ref->file->owner->username) ?>">
                    <?= htmlReady($file_ref->file->owner->getFullName()) ?>
                </a>
            <? else: ?>
                <?= htmlReady($file_ref->file->owner->getFullName()) ?>
            <? endif; ?>
            </td>
            <td title="<?= strftime('%x %X', $file_ref->file->mkdate) ?>" data-sort-value="<?= $file_ref->file->mkdate ?>">
                <?= reltime($file_ref->file->mkdate) ?>
            </td>
            <td class="options">
            <? if ($full_access): ?>
                <a href="<?= $controller->url_for('file/edit/' . $file_ref->id) ?>" data-dialog="size=auto" title="<?= _('Datei bearbeiten') ?>">
                    <?= Icon::create('edit', 'clickable')->asImg(16, ["alt" => _('bearbeiten')]) ?>
                </a>
            <? endif; ?>
                <a href="<?= $file_ref->getDownloadURL() ?>" title="<?= _('Datei herunterladen') ?>">
                    <?= Icon::create('download', 'clickable')->asImg(16, ["alt" => _('herunterladen')]) ?>
                </a>
            <? if ($full_access): ?>
                <a href="<?= $controller->url_for('file/move/' . $file_ref->id) ?>" data-dialog="size=auto" title="<?= _('Datei verschieben') ?>">
                    <?= Icon::create('file+move_right', 'clickable')->asImg(16, ["alt" => _('verschieben')]) ?>
                </a>
                <a href="<?= $controller->url_for('file/copy/' . $file_ref->id) ?>" data-dialog="size=auto" title="<?= _('Datei kopieren') ?>">
                    <?= Icon::create('file+add', 'clickable')->asImg(16, ["alt" => _('kopieren')]) ?>
                </a>
                <a href="<?= $controller->url_for('file/delete/' . $file_ref->id) ?>" title="<?= _('Datei löschen') ?>">
                    <?= Icon::create('trash', 'clickable')->asImg(16, ["alt" => _('löschen')]) ?>
                </a>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">
        <? if ($full_access || extension_loaded('zip')): ?>
                <?= _('Alle markierten') ?>
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
            <td colspan="3" class="actions">
                <?= $GLOBALS['template_factory']->render('shared/pagechooser', array(
                        'perPage'      => $limit,
                        'num_postings' => $filecount,
                        'page'         => $page,
                        'pagelink'     => $controller->url_for('/index/' . $dir_id . '/%u')
                    ))
                ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>
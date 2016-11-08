<form method="post">
<table class="default documents" data-folder_id="<?= htmlReady($topFolder->getId()) ?>">
    <caption>
        <div class="caption-container">
            <? $full_access = true;
                $breadcrumbs = array();
                $folder = $topFolder;
                do {
                    $breadcrumbs[] = $folder;
                } while ($folder = $folder->getParent());
                $breadcrumbs = array_reverse($breadcrumbs);
                $root_dir = array_shift($breadcrumbs);
                $last_crumb = end($breadcrumbs); ?>
            <div>
                <a href="<?= $controller->link_for('/index/' . $root_dir->getId()) ?>">
                    <?= Icon::create('folder-parent', 'clickable')->asImg(24) ?>
                    <? if (count($breadcrumbs) < 6): ?><?= htmlReady($root_dir->name) ?><? endif ?>
                </a>
                <? if(!empty($breadcrumbs)): ?>
                    <? if (count($breadcrumbs) > 5): ?>/...<?
                        $breadcrumbs = array_slice($breadcrumbs, count($breadcrumbs) - 5, 5);
                        ?><? endif ?>

                    <? foreach ($breadcrumbs as $crumb): ?>
                            /<a href="<?= $controller->url_for('/index/' . $crumb->getId()) ?>">
                                <?= htmlReady($crumb->name) ?>
                            </a>
                    <? endforeach ?>
                <? endif ?>
            </div>
        </div>
        <? if ($last_crumb->description) : ?>
        <small><?= htmlReady($last_crumb->description) ?></small>
        <? endif; ?>
    </caption>
    <?= $this->render_partial("files/_files_thead.php") ?>
    <? if (!$isRoot) : ?>
    <tbody>
        <? if(($parent_id) && ($parent_id != $folder_id)): ?>
        <tr class="chdir-up" <? if ($full_access) printf('data-folder="%s"', $folder_id) ?> data-sort-fixed>
            <td>&nbsp;</td>
            <td class="document-icon">
                <a href="<?= $controller->url_for('/index/' . $parent_id, $parent_page ) ?>">
                    <?//= Icon::create('arr_1up', 'clickable', ['title' => _('Ein Verzeichnis nach oben wechseln')])->asImg(24) ?>
                </a>
            </td>
            <td colspan="5">
                <a href="<?= $controller->url_for('/index/' . $parent_id, $parent_page) ?>" title="<?= _('Ein Verzeichnis nach oben wechseln') ?>">
                    <small><?= _('Ein Verzeichnis nach oben wechseln') ?></small>
                </a>
            </td>
        </tr>
        <? endif ?>
    </tbody>
<? endif; ?>
<? if (count($topFolder->getSubfolders()) + count($topFolder->getFiles()) === 0): ?>
    <tbody>
        <tr>
            <td colspan="8" class="empty">
                <?= _('Dieser Ordner ist leer') ?>
            </td>
        </tr>
    </tbody>
<? elseif (count($topFolder->getSubfolders())) : ?>
    <tbody>
    <? foreach ($topFolder->getSubfolders() as $folder) : ?>
        <? $owner = User::find($folder->user_id) ?: new User() ?>
        <tr <? if ($full_access) printf('data-file="%s"', $folder->getId()) ?> <? if ($full_access) printf('data-folder="%s"', $folder->getId()); ?>>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $folder->getId() ?>" <? if (in_array($folder->getId(), $marked_element_ids)) echo 'checked'; ?>>
            </td>
            <td class="document-icon" data-sort-value="0">
                <a href="<?= $controller->url_for('document/files/index/' . $folder->getId()) ?>">
                <? if ($is_empty): ?>
                    <?= Icon::create('folder-empty', 'clickable')->asImg(24) ?>
                <? else: ?>
                    <?= Icon::create('folder-full', 'clickable')->asImg(24) ?>
                <? endif; ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->url_for('/index/' . $folder->getId()) ?>">
                    <?= htmlReady($folder->name) ?>
                </a>
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
                    <?= htmlReady($owner->getFullName()) ?>
                </a>
            <? else: ?>
                <?= htmlReady($owner->getFullName()) ?>
            <? endif; ?>
            </td>
            <td title="<?= strftime('%x %X', $folder->mkdate) ?>" data-sort-value="<?= $folder->mkdate ?>" class="responsive-hidden">
                <?= reltime($folder->mkdate) ?>
            </td>
            <td class="actions">
                <? $actionMenu = ActionMenu::get() ?>
                <? if ($full_access): ?>
                    <? $actionMenu->addLink($controller->url_for('folder/edit/' . $folder->getId()),
                            _('Ordner bearbeiten'),
                            Icon::create('edit', 'clickable'),
                            ['data-dialog' => 'size=auto; reload-on-close']) ?>
                <? endif; ?>
                <? $actionMenu->addLink($downloadlink,
                        _('Ordner herunterladen'),
                        Icon::create('download', 'clickable')) ?>
                <? if ($full_access): ?>
                    <? $actionMenu->addLink($controller->url_for('folder/move/' . $folder->getId()),
                            _('Ordner verschieben'),
                            Icon::create('folder-empty+move_right', 'clickable'),
                            ['data-dialog' => 'reload-on-close']) ?>
                    <? $actionMenu->addLink($controller->url_for('folder/copy/' . $folder->getId()),
                            _('Ordner kopieren'),
                            Icon::create('folder-empty+add', 'clickable'),
                            ['data-dialog' => 'reload-on-close']) ?>
                    <? $actionMenu->addLink($controller->url_for('folder/delete/' . $folder->getId()),
                            _('Ordner löschen'),
                            Icon::create('trash', 'clickable'),
                            ['data-confirm' => sprintf(_('Soll den Ordner "%s" wirklich gelöscht werden?'), htmlReady($folder->name)),
                             'data-dialog' => 'size=auto; reload-on-close',
                             'formaction' => $controller->url_for('folder/delete/' . $folder->getId())]) ?>
                <? endif; ?>
                <?= $actionMenu->render() ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
<? endif; ?>
<? if (count($topFolder->getFiles())) : ?>
    <tbody>
    <? foreach ($topFolder->getFiles() as $file_ref) : ?>
        <?= $this->render_partial("files/_fileref_tr", ['file_ref' => $file_ref, 'current_folder' => $topFolder]) ?>
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
            <span class="responsive-visible">
                <?= Studip\LinkButton::create(_("Neuer Ordner"), URLHelper::getUrl(
                    'dispatch.php/folder/new',
                    array('parent_folder_id' => $topFolder->getId())
                ), array('data-dialog' => 'reload-on-close;size=auto')) ?>
                <?= Studip\LinkButton::create(_("Datei hinzufügen"), "#", array('onClick' => "STUDIP.Files.openAddFilesWindow(); return false;")) ?>
            </span>
        <? endif; ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>

<?= $this->render_partial("files/upload_window.php") ?>
<?= $this->render_partial("files/add_files_window.php", array('folder_id' => $topFolder->getId(), 'hidden' => true)) ?>
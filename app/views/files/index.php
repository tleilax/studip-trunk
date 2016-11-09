<form method="post">
<table class="default documents" data-folder_id="<?= htmlReady($topFolder->getId()) ?>">
    <caption>
        <div class="caption-container">
            <?
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
        <? if (!$folder->isVisible($GLOBALS['user']->id)) continue; ?>
        <? $is_readable = $folder->isReadable($GLOBALS['user']->id) ?>
        <? $owner = User::find($folder->user_id) ?: new User() ?>
        <? $is_empty = count($folder->getSubfolders()) + count($folder->getFiles()) == 0 ?>
        <? $foldershape = call_user_func([get_class($folder), 'getIconShape']) ?>
        <tr>
            <td>
                <? if ($is_readable) : ?>
                    <input type="checkbox" name="ids[]" value="<?= $folder->getId() ?>" <? if (in_array($folder->getId(), $marked_element_ids)) echo 'checked'; ?>>
                <? endif?>
            </td>
            <td class="document-icon" data-sort-value="0">
                <? if ($is_readable) : ?>
                    <a href="<?= $controller->url_for('document/files/index/' . $folder->getId()) ?>">
                <? endif ?>
                <? if ($is_empty): ?>
                    <?= Icon::create($foldershape . '-empty', $is_readable ? 'clickable': '')->asImg(24) ?>
                <? else: ?>
                    <?= Icon::create($foldershape . '-full', $is_readable ? 'clickable': '')->asImg(24) ?>
                <? endif; ?>
                <? if ($is_readable) : ?>
                </a>
                <? endif ?>
            </td>
            <td>
                <? if ($is_readable) : ?>
                    <a href="<?= $controller->url_for('/index/' . $folder->getId()) ?>">                       <? endif ?>
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
                    <? $actionMenu->addLink($controller->url_for('folder/edit/' . $folder->getId()),
                            _('Ordner bearbeiten'),
                            Icon::create('edit', 'clickable'),
                            ['data-dialog' => 'size=auto; reload-on-close']) ?>
                <? endif; ?>
                <? $actionMenu->addLink($downloadlink,
                        _('Ordner herunterladen'),
                        Icon::create('download', 'clickable')) ?>
                <? if ($folder->isWritable($GLOBALS['user']->id)): ?>
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
<? if (count($topFolder->getFiles()) && $topFolder->isReadable($GLOBALS['user']->id)) : ?>
    <tbody class="files">
    <? foreach ($topFolder->getFiles() as $file_ref) : ?>
        <?= $this->render_partial("files/_fileref_tr", ['file_ref' => $file_ref, 'current_folder' => $topFolder]) ?>
    <? endforeach; ?>
    </tbody>
<? endif; ?>
    <tfoot>
        <tr>
            <td colspan="100">
            <?= Studip\Button::create(_('Herunterladen'), 'download') ?>
            <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                <?= Studip\Button::create(_('Verschieben'), 'move', array('data-dialog' => '')) ?>
                <?= Studip\Button::create(_('Löschen'), 'delete', array('data-dialog' => '')) ?>
            <? endif; ?>

                <? if ($topFolder->isReadable($GLOBALS['user']->id)): ?>
                <?= Studip\Button::create(_('Kopieren'), 'copy', array('data-dialog' => ''))?>
                <? endif; ?>
            <span class="responsive-visible">
                <? if ($topFolder->isSubfolderAllowed($GLOBALS['user']->id)): ?>
                <?= Studip\LinkButton::create(_("Neuer Ordner"), URLHelper::getUrl(
                    'dispatch.php/folder/new',
                    array('parent_folder_id' => $topFolder->getId())
                ), array('data-dialog' => '')) ?>
                <? endif ?>
                <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                <?= Studip\LinkButton::create(_("Datei hinzufügen"), "#", array('onClick' => "STUDIP.Files.openAddFilesWindow(); return false;")) ?>
                <? endif ?>
            </span>
            </td>
        </tr>
    </tfoot>
</table>
</form>

<?= $this->render_partial("files/upload_window.php") ?>
<?= $this->render_partial("files/add_files_window.php", array('folder_id' => $topFolder->getId(), 'hidden' => true)) ?>
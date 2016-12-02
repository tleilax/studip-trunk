<? $controllerpath = ($topFolder->range_type === "user" ? "" : $topFolder->range_type."/").'files/index' ?>
<form method="post" action="<?= URLHelper::getLink('dispatch.php/files/bulk') ?>">
<?= CSRFProtection::tokenTag() ?>
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
                <a href="<?= $controller->link_for($controllerpath . '/' . $root_dir->getId()) ?>" title="<?= _("Zum Hauptordner") ?>">
                    <?= Icon::create('folder-home-full', 'clickable')->asImg(30, array('class' => "text-bottom")) ?>
                    <? if (count($breadcrumbs) < 6): ?><?= htmlReady($root_dir->name) ?><? endif ?>
                </a>
                <? if(!empty($breadcrumbs)): ?>
                    <? if (count($breadcrumbs) > 5): ?>/...<?
                        $breadcrumbs = array_slice($breadcrumbs, count($breadcrumbs) - 5, 5);
                        ?><? endif ?>

                    <? foreach ($breadcrumbs as $crumb): ?>
                            /<a href="<?= $controller->url_for($controllerpath . '/' . $crumb->getId()) ?>">
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
        <?= $this->render_partial('files/_folder_tr', ['folder' => $folder, 'marked_element_ids' => $marked_element_ids]) ?>
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

<?= $this->render_partial("file/upload_window.php") ?>
<?= $this->render_partial("file/add_files_window.php", array('folder_id' => $topFolder->getId(), 'hidden' => true)) ?>
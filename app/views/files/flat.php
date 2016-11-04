<form method="post">
<table class="default documents" data-folder_id="<?= htmlReady($topFolder->getId()) ?>">
    <?= $this->render_partial("files/_files_thead.php") ?>
<? if (count($files) === 0): ?>
    <tbody>
        <tr>
            <td colspan="8" class="empty">
                <?= _('Keine Dateien vorhanden.') ?>
            </td>
        </tr>
    </tbody>
<? elseif (count($files)) : ?>
    <tbody>
    <? foreach ($files as $file_ref) : ?>
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

<? ob_start(); ?>
<div align="center">
<input class="tablesorterfilter" placeholder="Name" data-column="2" type="search"><br>
<input class="tablesorterfilter" placeholder="Autor/in" data-column="4" type="search"><br>
<input class="tablesorterfilter" placeholder="Datum" data-column="5" type="search"><br>
</div>
<? $content = ob_get_clean();
$sidebar = Sidebar::get();
$widget = new SidebarWidget();
$widget->setTitle(_('Filter'));
$widget->addElement(new WidgetElement($content));
$sidebar->addWidget($widget); ?>
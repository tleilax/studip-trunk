<?=$last_visitdate?>
<form method="post">
<table class="default documents">
    <?= $this->render_partial("files/_files_thead.php") ?>
<? if (count($files) === 0): ?>
    <tbody>
        <tr>
            <td colspan="8" class="empty">
                <?= _('Keine Dateien vorhanden.') ?>
            </td>
        </tr>
    </tbody>
<? elseif (count($files)): ?>
    <tbody>
    <? foreach ($files as $file_ref): ?>
        <?= $this->render_partial("files/_fileref_tr", ['file_ref' => $file_ref, 'current_folder' => $file_ref->folder->getTypedFolder()]) ?>
    <? endforeach ?>
    </tbody>
<? endif; ?>
    <tfoot>
        <tr>
            <td colspan="100">
            <?= Studip\Button::create(_('Herunterladen'), 'download') ?>
            <?= Studip\Button::create(_('Kopieren'), 'copy', array('data-dialog' => '')) ?>

            <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                <?= Studip\Button::create(_('Verschieben'), 'move', array('data-dialog' => '')) ?>
                <?= Studip\Button::create(_('Löschen'), 'delete', array('data-dialog' => '')) ?>
            <? endif ?>
           </td>
        </tr>
    </tfoot>
</table>
</form>

<? ob_start(); ?>
<div align="center">
<input class="tablesorterfilter filter-select" placeholder="Name" data-column="2" type="search" style="width: 100%; margin-bottom: 5px;"><br>
<input class="tablesorterfilter" placeholder="Autor/in" data-column="4" type="search" style="width: 100%; margin-bottom: 5px;"><br>
<select class="tablesorterfilter filter-select" data-column="5">
<option></option>
<option value="<10000">kleiner 10kB</option>
<option value="1">1</option>
</select>
</div>
<? $content = ob_get_clean();
$sidebar = Sidebar::get();
$widget = new SidebarWidget();
$widget->setTitle(_('Filter'));
$widget->addElement(new WidgetElement($content));
$sidebar->addWidget($widget); ?>
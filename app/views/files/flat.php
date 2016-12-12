<form method="post" action="<?= URLHelper::getLink('dispatch.php/files/bulk') ?>">
<table class="default documents sortable-table flat" data-sortlist="[[5, 1]]">
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
            
            <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                <?= Studip\Button::create(_('Verschieben'), 'move', array('data-dialog' => '')) ?>
            <? endif ?>
            
            <?= Studip\Button::create(_('Kopieren'), 'copy', array('data-dialog' => '')) ?>

            <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                <?= Studip\Button::create(_('Löschen'), 'delete', array('data-confirm' => _('Soll die Auswahl wirklich gelöscht werden?'))) ?>
            <? endif ?>
           </td>
        </tr>
    </tfoot>
</table>
</form>

<? ob_start(); ?>
<div align="center">
<input class="tablesorterfilter" placeholder="<?=_('Name oder Autor/-in')?>" data-column="2,4" type="search" style="width: 100%; margin-bottom: 5px;"><br>
</div>
<? $content = ob_get_clean();
$sidebar = Sidebar::get();
$widget = new SidebarWidget();
$widget->setTitle(_('Filter'));
$widget->addElement(new WidgetElement($content));
$sidebar->addWidget($widget);
?>
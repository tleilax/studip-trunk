<form method="post" action="<?= $controller->link_for('file/bulk/' . $topFolder->getId()) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default documents sortable-table flat" data-sortlist="[[5, 1]]">
        <?= $this->render_partial('files/_files_thead.php') ?>
        <tbody>
        <? if (count($files) === 0): ?>
            <tr>
                <td colspan="7" class="empty">
                    <?= _('Keine Dateien vorhanden.') ?>
                </td>
            </tr>
        <? else: ?>
            <? foreach ($files as $file_ref): ?>
                <?= $this->render_partial('files/_fileref_tr', [
                    'file_ref'       => $file_ref,
                    'current_folder' => $file_ref->folder->getTypedFolder(),
                ]) ?>
            <? endforeach ?>
        <? endif; ?>
        </tbody>
        <? if ($GLOBALS['user']->id !== 'nobody') : ?>
        <tfoot>
        <tr>
            <td colspan="7">
                <span class="multibuttons">
                    <?= Studip\Button::create(_('Herunterladen'), 'download', ['disabled' => '']) ?>

                    <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                        <?= Studip\Button::create(_('Verschieben'), 'move', ['data-dialog' => '', 'disabled' => '']) ?>
                    <? endif ?>

                    <?= Studip\Button::create(_('Kopieren'), 'copy', ['data-dialog' => '', 'disabled' => '']) ?>

                    <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                        <?= Studip\Button::create(_('Löschen'), 'delete', ['data-confirm' => _('Soll die Auswahl wirklich gelöscht werden?'), 'disabled' => '']) ?>
                    <? endif ?>
                 </span>
           </td>
        </tr>
        </tfoot>
        <? endif ?>
</table>
</form>

<? ob_start(); ?>
<div align="center">
<input class="tablesorterfilter" placeholder="<?= _('Name oder Autor/-in') ?>" data-column="2,4" type="search" style="width: 100%; margin-bottom: 5px;"><br>
</div>
<? $content = ob_get_clean();
$widget = new SidebarWidget();
$widget->setTitle(_('Filter'));
$widget->addElement(new WidgetElement($content));
Sidebar::get()->addWidget($widget);

$views = new ViewsWidget();
$views->addLink(
    _('Ordneransicht'),
    $controller->url_for(($range_type ? $range_type . '/' : '') . 'files/index'),
    null,
    [],
    'index'
);
$views->addLink(
    _('Alle Dateien'),
    $controller->url_for(($range_type ? $range_type.'/' : '') . 'files/flat'),
    null,
    [],
    'flat'
)->setActive(true);
Sidebar::get()->addWidget($views);

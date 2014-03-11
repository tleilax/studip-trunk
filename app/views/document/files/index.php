<? if (isset($flash['question'])): ?>
    <?= $flash['question'] ?>
<? endif; ?>

<form action="<?= $controller->url_for('document/files/bulk/' . $dir_id) ?>" method="post">
<table class="default documents">
    <caption>
    <? $last_crumb = end($breadcrumbs); ?>
        <div class="bread-crumbs <? if (count($breadcrumbs) > 1) echo 'extendable'; ?>">
            <a href="<?= $controller->url_for('document/files/index/' . $last_crumb['id']) ?>">
                <?= Assets::img('icons/24/blue/folder-down.png') ?>
            </a>
        <? if (count($breadcrumbs) > 1): ?>
            <ul>
            <? foreach (array_slice($breadcrumbs, 0, -1) as $crumb): ?>
                <li>
                    <a href="<?= $controller->url_for('document/files/index/' . $crumb['id']) ?>">
                        <?= htmlReady($crumb['name']) ?>
                    </a>
                </li>
            <? endforeach; ?>
            </ul>
        <? endif; ?>
        </div>
        <header class="folder-description">
            <h2><?= htmlReady($last_crumb['name']) ?></h2>
        <? if ($last_crumb['description']): ?>
            <p><?= formatReady($last_crumb['description']) ?></p>
        <? endif; ?>
        </header>
    </caption>
    <colgroup>
        <col width="20px">
        <col width="20px">
        <col>
        <col width="100px">
        <col width="150px">
        <col width="120px">
        <col width="100px">
    </colgroup>
    <thead>
        <th>
            <input type="checkbox" data-proxyfor=":checkbox[name='ids[]']">
        </th>
        <th><?= _('Typ') ?></th>
        <th><?= _('Name') ?></th>
        <th><?= _('Größe') ?></th>
        <th><?= _('Autor/in') ?></th>
        <th><?= _('Datum') ?></th>
        <th>&nbsp;</th>
    </thead>
    <tbody>
<? if (!$directory->isRootDirectory()): ?>
        <tr class="chdir-up">
            <td>&nbsp;</td>
            <td class="document-icon">
                <a href="<?= $controller->url_for('document/files/index/' . $parent_id) ?>">
                    <?= Assets::img('icons/24/blue/arr_1up.png', tooltip2(_('Eine Ordner-Ebene höher springen'))) ?>
                </a>
            </td>
            <td colspan="5">
                <a href="<?= $controller->url_for('document/files/index/' . $parent_id) ?>">
                    ..
                </a>
            </td>
        </tr>
<? endif; ?>
<? if (empty($files)): ?>
        <tr>
            <td colspan="7" class="empty">
                <?= _('Dieser Ordner ist leer') ?>
            </td>
        </tr>
<? else: ?>
    <? foreach ($files as $file): ?>
        <tr>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $file->id ?>" <? if (in_array($file->id, $marked)) echo 'checked'; ?>>
            </td>
        <? if ($file->getFile() instanceof StudipDirectory): ?>
            <td class="document-icon">
                <a href="<?= $controller->url_for('document/files/index/' . $file->id) ?>">
                <? if ($file->getFile()->isEmpty()): ?>
                    <?= Assets::img('icons/24/blue/folder-empty.png') ?>
                <? else: ?>
                    <?= Assets::img('icons/24/blue/folder-full.png') ?>
                <? endif; ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->url_for('document/files/index/' . $file->id) ?>">
                    <?= htmlReady($file->getFile()->filename) ?>
                </a>
            <? if ($file->getDescription()): ?>
                <small><?= htmlReady($file->getDescription()) ?></small>
            <? endif; ?>
            </td>
            <td><?= sprintf(ngettext('%u Eintrag', '%u Einträge', $count = $file->getFile()->countFiles()), $count) ?></td>
            <td><?= htmlReady(User::find($file->getFile()->user_id)->getFullName()) ?></td>
            <td title="<?= strftime('%x %X', $file->getFile()->mkdate) ?>">
                <?= reltime($file->getFile()->mkdate) ?>
            </td>
            <td class="options">
                <a href="<?= $controller->url_for('document/folder/edit/' . $file->id) ?>" rel="lightbox">
                    <?= Assets::img('icons/16/blue/edit.png', tooltip2(_('Ordner bearbeiten'))) ?>
                </a>
                <a href="<?= $controller->url_for('document/folder/download/' . $file->id) ?>">
                    <?= Assets::img('icons/16/blue/download.png', tooltip2(_('Ordner herunterladen'))) ?>
                </a>
                <a href="<?= $controller->url_for('document/folder/move/' . $file->id) ?>">
                    <?= Assets::img('icons/16/blue/move_right/folder-empty.png', tooltip2(_('Ordner verschieben'))) ?>
                </a>
                <a href="<?= $controller->url_for('document/folder/delete/' . $file->id) ?>">
                    <?= Assets::img('icons/16/blue/trash.png', tooltip2(_('Ordner löschen'))) ?>
                </a>
            </td>
        <? else: ?>
            <td class="document-icon">
                <a href="<?= $controller->url_for('document/files/download/' . $file->id . '/inline') ?>">
                    <?= Assets::img('icons/24/blue/'. get_icon_for_mimetype($file->getFile()->getMimeType())) ?>
                </a>
            </td>
            <td>
                <a href="<?= $controller->url_for('document/files/download/' . $file->id) ?>" title="<?= htmlReady($file->getFile()->filename) ?>">
                    <?= htmlReady($file->name) ?>
                </a>
            <? if ($file->getFile()->restricted): ?>
                <?= Assets::img('icons/16/blue/lock-locked.png', array('class' => 'text-top') + tooltip2(_('Diese Datei ist nicht frei von Rechten Dritter.'))) ?>
            <? endif; ?>
            <? if ($file->getDescription()): ?>
                <small><?= htmlReady($file->getDescription()) ?></small>
            <? endif; ?>
            </td>
            <td title="<?= number_format($file->getFile()->size, 0, ',', '.') . ' Byte' ?>">
                <?= relSize($file->getFile()->size, false) ?>
            </td>
            <td><?= htmlReady(User::find($file->getFile()->user_id)->getFullName()) ?></td>
            <td title="<?= strftime('%x %X', $file->getFile()->mkdate) ?>">
                <?= reltime($file->getFile()->mkdate) ?>
            </td>
            <td class="options">
                <a href="<?= $controller->url_for('document/files/edit/' . $file->id) ?>" rel="lightbox">
                    <?= Assets::img('icons/16/blue/edit.png', tooltip2(_('Datei bearbeiten'))) ?>
                </a>
                <a href="<?= $controller->url_for('document/files/download/' . $file->id) ?>">
                    <?= Assets::img('icons/16/blue/download.png', tooltip2(_('Datei herunterladen'))) ?>
                </a>
                <a href="<?= $controller->url_for('document/files/move/' . $file->id) ?>">
                    <?= Assets::img('icons/16/blue/move_right/file.png', tooltip2(_('Datei verschieben'))) ?>
                </a>
                <a href="<?= $controller->url_for('document/files/delete/' . $file->id) ?>">
                    <?= Assets::img('icons/16/blue/trash.png', tooltip2(_('Datei löschen'))) ?>
                </a>
            </td>
        <? endif; ?>
        </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="printhead">
                <?= _('Alle markierten') ?>
                <?= Studip\Button::create(_('Herunterladen'), 'download') ?>
                <?= Studip\Button::create(_('Verschieben'), 'move') ?>
                <?= Studip\Button::create(_('Löschen'), 'delete') ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>

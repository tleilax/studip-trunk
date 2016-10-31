<div style="text-align: center; margin-bottom: 20px;">
    <?= _("Kopieren nach") ?>
        <?= Icon::create("folder-full", "info")->asImg("20px", array('class' => "text-bottom")) ?>
        <?= htmlReady($to_folder->parent_id ? $to_folder->name : _("Hauptordner")) ?>
</div>
<?
$options = array();
if (Request::get("to_plugin")) {
    $options['to_plugin'] = Request::get("to_plugin");
}
if (Request::get("plugin")) {
    $options['plugin'] = Request::get("plugin");
}
if (Request::get("to_folder_id")) {
    $options['to_folder_id'] = Request::get("to_folder_id");
}
?>
<table class="default">
    <thead>
        <tr>
            <th><?= _("Typ") ?></th>
            <th><?= _("Name") ?></th>
        </tr>
    </thead>
    <? if ($top_folder['parent_id'] && ($top_folder['parent_id'] !== $top_folder->getId())) : ?>
        <tbody>
            <tr>
                <td colspan="2">
                    <a href="<?= $controller->url_for('/choose_file/' . $top_folder['parent_id'], $options) ?>" title="<?= _('Ein Verzeichnis nach oben wechseln') ?>" data-dialog>
                        <small><?= _('Ein Verzeichnis nach oben wechseln') ?></small>
                    </a>
                </td>
            </tr>
        </tbody>
    <? endif ?>
    <? if (count($top_folder->subfolders) + count($top_folder->file_refs) === 0): ?>
        <tbody>
        <tr>
            <td colspan="2" class="empty">
                <?= _('Dieser Ordner ist leer') ?>
            </td>
        </tr>
        </tbody>
    <? else : ?>
        <? foreach ($top_folder->subfolders as $folder) : ?>
            <tr <? if ($full_access) printf('data-file="%s"', $folder->id) ?> <? if ($full_access) printf('data-folder="%s"', $folder->id); ?>>
                <td class="document-icon" data-sort-value="0">
                    <a href="<?= $controller->link_for('/choose_file/' . $folder->id, $options) ?>" data-dialog>
                        <? if ($is_empty): ?>
                            <?= Icon::create('folder-empty', 'clickable')->asImg(24) ?>
                        <? else: ?>
                            <?= Icon::create('folder-full', 'clickable')->asImg(24) ?>
                        <? endif; ?>
                    </a>
                </td>
                <td>
                    <a href="<?= $controller->link_for('/choose_file/' . $folder->id, $options) ?>" data-dialog>
                        <?= htmlReady($folder->name) ?>
                    </a>
                    <? if ($folder->description): ?>
                        <small class="responsive-hidden"><?= htmlReady($folder->description) ?></small>
                    <? endif; ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
        <? if (count($top_folder->file_refs)) : ?>
            <tbody>
            <? foreach ($top_folder->file_refs as $fileref) : ?>
                <tr>
                    <td class="document-icon" data-sort-value="1">
                        <form action="<?= $controller->link_for('/choose_file/' . $folder->id, $options) ?>" method="post" data-dialog>
                            <input type="hidden" name="file_id" value="<?= htmlReady($fileref->getId()) ?>">
                            <a href="#" onClick="jQuery(this).closest('form').submit(); return false;">
                                <?= Icon::create(get_icon_for_mimetype($fileref->file->mime_type), 'clickable')->asImg(24) ?>
                            </a>
                        </form>
                    </td>
                    <td>
                        <form action="<?= $controller->link_for('/choose_file/' . $folder->id, $options) ?>" method="post" data-dialog>
                            <input type="hidden" name="file_id" value="<?= htmlReady($fileref->getId()) ?>">
                            <a href="#" onClick="jQuery(this).closest('form').submit(); return false;">
                                <?= htmlReady($fileref->file->name) ?>
                            </a>
                        </form>
                    </td>
                </tr>
            <? endforeach; ?>
            </tbody>
        <? endif ?>
    <? endif ?>
</table>

<? var_dump($controller->url_for('/add_files_window/' . Request::get("to_folder_id"))) ?>
<?
$options = array();
if (Request::get("to_plugin")) {
    $options['to_plugin'] = Request::get("to_plugin");
}
?>
<div data-dialog-button>
    <?= Studip\LinkButton::create(_("zurück"), $controller->url_for('/add_files_window/' . Request::get("to_folder_id"), $options), array('data-dialog' => 1)) ?>
</div>
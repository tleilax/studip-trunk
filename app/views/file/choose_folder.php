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
if (Request::get("fileref_id")) {
    $options['fileref_id'] = Request::get("fileref_id");
}
if (Request::get("copymode")) {
    $options['copymode'] = Request::get("copymode");
}
?>

<? /*if ($filesystemplugin && $filesystemplugin->hasSearch()) : ?>
    <form action="<?= $controller->url_for('/choose_file/' . $top_folder->parent_id) ?>" method="get" class="default" data-dialog style="margin-bottom: 50px;">
        <? foreach ($options as $key => $value) : ?>
            <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value) ?>">
        <? endforeach ?>
        <? $request_parameter = Request::getArray("parameter") ?>
        <input type="text" name="search" value="<?= htmlReady(Request::get("search")) ?>" placeholder="<?= _("Suche nach ...") ?>" style="max-width: 100%;">

        <? foreach ((array) $filesystemplugin->getSearchParameters() as $parameter) : ?>
            <label>
                <? switch ($parameter['type']) {
                    case "text": ?>
                        <?= htmlReady($parameter['label']) ?>
                        <input type="text" name="parameter[<?= htmlReady($parameter['name']) ?>]" value="<?= htmlReady($request_parameter[$parameter['name']]) ?>" placeholder="<?= htmlReady($parameter['placeholder']) ?>">
                        <? break ?>
                    <? case "select": ?>
                        <?= htmlReady($parameter['label']) ?>
                        <select name="parameter[<?= htmlReady($parameter['name']) ?>]">
                            <? foreach ($parameter['options'] as $index => $option) : ?>
                                <option value="<?= htmlReady($index) ?>"<?= ($index === $request_parameter[$parameter['name']] ? " selected" : "") ?>><?= htmlReady($option) ?></option>
                            <? endforeach ?>
                        </select>
                        <? break ?>
                    <? case "checkbox": ?>
                        <input type="checkbox" name="parameter[<?= htmlReady($parameter['name']) ?>]" value="1"<?= $request_parameter[$parameter['name']] ? " checked" : "" ?>>
                        <?= htmlReady($parameter['label']) ?>
                        <? break ?>
                <? } ?>
            </label>
        <? endforeach ?>
    </form>

<? endif*/ ?>

<? if ($top_folder) : ?>
<table class="default">
    <thead>
        <tr>
            <th><?= _("Typ") ?></th>
            <th><?= _("Name") ?></th>
        </tr>
    </thead>
    <? if ($top_folder->parent_id && ($top_folder->parent_id !== $top_folder->getId())) : ?>
        <tbody>
            <tr>
                <td colspan="2">
                    <a href="<?= $controller->url_for('/choose_folder/' . $top_folder->parent_id, $options) ?>" title="<?= _('Ein Verzeichnis nach oben wechseln') ?>" data-dialog>
                        <small><?= _('Ein Verzeichnis nach oben wechseln') ?></small>
                    </a>
                </td>
            </tr>
        </tbody>
    <? endif ?>
    <? if (count($top_folder->getSubfolders()) + count($top_folder->getFiles()) === 0): ?>
        <tbody>
        <tr>
            <td colspan="2" class="empty">
                <?= _('Dieser Ordner ist leer') ?>
            </td>
        </tr>
        </tbody>
    <? else : ?>
        <? foreach ($top_folder->getSubfolders() as $subfolder) : ?>
            <tr <? if ($full_access) printf('data-file="%s"', $subfolder->getId()) ?> <? if ($full_access) printf('data-folder="%s"', $subfolder->id); ?>>
                <td class="document-icon" data-sort-value="0">
                    <? if ($subfolder->isReadable($GLOBALS['user']->id)) : ?>
                    <a href="<?= $controller->link_for('/choose_folder/' . $subfolder->getId(), $options) ?>" data-dialog>
                    <? endif ?>
                        <? if ($is_empty): ?>
                            <?= Icon::create('folder-empty', 'clickable')->asImg(24) ?>
                        <? else: ?>
                            <?= Icon::create('folder-full', 'clickable')->asImg(24) ?>
                        <? endif; ?>
                    <? if ($subfolder->isReadable($GLOBALS['user']->id)) : ?>
                    </a>
                    <? endif ?>
                </td>
                <td>
                    <? if ($subfolder->isReadable($GLOBALS['user']->id)) : ?>
                    <a href="<?= $controller->link_for('/choose_folder/' . $subfolder->id, $options) ?>" data-dialog>
                    <? endif ?>
                        <?= htmlReady($subfolder->name) ?>
                    <? if ($subfolder->isReadable($GLOBALS['user']->id)) : ?>
                    </a>
                    <? endif ?>
                    <? if ($subfolder->description): ?>
                        <small class="responsive-hidden"><?= htmlReady($subfolder->description) ?></small>
                    <? endif; ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    <? endif ?>
</table>
<? endif ?>
<? if ($top_folder->isWritable($GLOBALS['user']->id)) : ?>
    <form action="<?= $controller->link_for('/choose_folder/' . $top_folder->getId(), $options) ?>" method="post">
        <? if ($options['copymode'] == 'copy'): ?>
        	<?= Studip\Button::createAccept(_('hierher kopieren'), 'do_action', array()); ?>
        <? else: ?>
        	<?= Studip\Button::createAccept(_('hierher verschieben'), 'do_action', array()); ?>
        <? endif; ?>
    </form>
<? endif ?>
<?
$options = array();
if (Request::get("to_plugin")) {
    $options['to_plugin'] = Request::get("to_plugin");
}
?>
<div data-dialog-button>	
    <?= Studip\LinkButton::create(_("Zur�ck"), $controller->url_for('/copy_files_window/', $options), array('data-dialog' => 1)) ?>
</div>
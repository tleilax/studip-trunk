<?
$options = array();
if (Request::get("to_plugin")) {
    $options['to_plugin'] = Request::get("to_plugin");
}
if (Request::get("to_folder_id")) {
    $options['to_folder_id'] = Request::get("to_folder_id");
}
if (Request::get("copymode") || $copymode) {
    $options['copymode'] = Request::get("copymode", $copymode);
}
if (Request::get("isfolder")) {
    $options['isfolder'] = Request::get("isfolder");
}
if ($fileref_id) {
    $options['fileref_id'] = $fileref_id;
}
$options['direct_parent'] = true;
?>
<div class="files_source_selector" <?= $hidden ? ' style="display: none;"' : "" ?>>
    <?= sprintf(_("Ziel zum %s auswählen"), $copymode === "move" ? _("Verschieben") : _("Kopieren")) ?>
    <div class="file_select_possibilities">
        <a href="<?= $controller->link_for("/choose_folder/" . $parent_folder->id , $options) ?>" data-dialog>
            <?= Icon::create("folder-parent", "clickable")->asImg(50) ?>
            <?= _("Aktueller Ordner") ?>
        </a>
        <a href="<?= $controller->link_for("/choose_folder/" . Folder::findTopFolder($GLOBALS['user']->id)->getId(), $options) ?>" data-dialog>
            <?= Icon::create("files", "clickable")->asImg(50) ?>
            <?= _("Meine Dateien") ?>
        </a>
        <a href="<?= $controller->link_for("/choose_folder_from_course/", $options) ?>" data-dialog>
            <?= Icon::create("seminar", "clickable")->asImg(50) ?>
            <?= _("Meine Veranstaltungen") ?>
        </a>
        <a href="<?= $controller->link_for("/choose_folder_from_institute/", $options) ?>" data-dialog>
            <?= Icon::create("institute", "clickable")->asImg(50) ?>
            <?= _("Meine Einrichtungen") ?>
        </a>
        <? foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) : ?>
            <? if ($plugin->isPersonalFileArea()) : ?>
                <? $nav = $plugin->getFileSelectNavigation() ?>
                <? if ($nav) : ?>
                    <a href="<?= $controller->link_for("/choose_folder/", array_merge($options, array('plugin' => get_class($plugin)))) ?>" data-dialog>
                        <?= $nav->getImage()->asImg(50) ?>
                        <?= htmlReady($nav->getTitle()) ?>
                    </a>
                <? endif ?>
            <? endif ?>
        <? endforeach ?>
    </div>


    <? if (!Request::isDialog()) : ?>
        <div>
            <?= \Studip\LinkButton::create(_("Abbrechen"), $controller->url_for(($parent_folder->range_type === "course" ? "course/" : ($folder->range_type === "institute" ? "institute/" : "")).'files/index/' . $parent_folder->id)) ?>
        </div>
    <? endif ?>
</div>

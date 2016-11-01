<div class="files_source_selector" data-folder_id="<?= htmlReady($folder_id) ?>"<?= $hidden ? ' style="display: none;"' : "" ?>>
    <?= _("Quelle auswählen") ?>
    <div class="file_select_possibilities">
        <a href="#" onClick="jQuery('.file_selector input[type=file]').first().click(); return false;">
            <?= Icon::create("computer", "clickable")->asImg(50) ?>
            <?= _("Mein Computer") ?>
        </a>
        <a href="<?= $controller->link_for("files/choose_file/" . Folder::findTopFolder($GLOBALS['user']->id)->getId(), array('to_folder_id' => $folder_id)) ?>" data-dialog>
            <?= Icon::create("files", "clickable")->asImg(50) ?>
            <?= _("Meine Dateien") ?>
        </a>
        <a href="">
            <?= Icon::create("seminar", "clickable")->asImg(50) ?>
            <?= _("Meine Veranstaltungen") ?>
        </a>
        <a href="">
            <?= Icon::create("computer", "clickable")->asImg(50) ?>
            <?= _("OwnCloud") ?>
        </a>
        <a href="">
            <?= Icon::create("literature", "clickable")->asImg(50) ?>
            <?= _("Bibliothek") ?>
        </a>
        <a href="">
            <?= Icon::create("service", "clickable")->asImg(50) ?>
            <?= _("Lernmaterialien") ?>
        </a>
        <? foreach (PluginManager::getInstance()->getPlugins('FilesystemPlugin') as $plugin) : ?>
            <? $nav = $plugin->getFileSelectNavigation() ?>
            <? if ($nav) : ?>
                <a href="<?= $controller->link_for("files/choose_file/", array('to_folder_id' => $folder_id, 'plugin' => get_class($plugin))) ?>" data-dialog>
                    <?= $nav->getImage()->asImg(50) ?>
                    <?= htmlReady($nav->getTitle()) ?>
                </a>
            <? endif ?>
        <? endforeach ?>
    </div>

    <form style="display: none;" class="file_selector">
        <input type="file" name="files[]" multiple onChange="STUDIP.Files.upload(this.files);">
    </form>
</div>
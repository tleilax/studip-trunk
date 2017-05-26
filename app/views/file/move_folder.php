<?= $this->render_partial('file/_folder_aside.php', compact('folder')) ?>

<div id="file_management_forms" style="width:79%; display:table;">
    <form class="default" action="<?= $controller->url_for('/' . ($copymode ? 'copy' : 'move') . '/' . $folder_id) ?>"
        <? if(Request::isDialog()) echo 'data-dialog="reload-on-close"'; ?>>

        <?= CSRFProtection::tokenTag() ?>
        <div id="copymove-destination">
            <label for="destination"><?= _('Ziel') ?></label>
            <select id="destination">
                <optgroup label="lokal">
                    <option value="myfiles"><?= _('Meine Dateien') ?></option>
                    <option value="courses"><?= _('Veranstaltungen') ?></option>
                    <option value="institutes"><?= _('Einrichtungen') ?></option>
                </optgroup>
                <optgroup label="extern">
                    <option disabled="disabled" value="plugin1"><?= _('Plugin1') ?></option>
                    <option disabled="disabled" value="plugin2"><?= _('Plugin2') ?></option>
                    <option disabled="disabled" value="plugin3"><?= _('Plugin3') ?></option>
                </optgroup>
            </select>
        </div>

        <div id="copymove-range" style="display: none;">
            <label for="range"><?= htmlReady($range_name) ?></label>
            <?= $search ?>
        </div>

        <div id="copymove-range-inst" style="display: none;">
            <label for="range"><?= htmlReady($range_name) ?></label>
            <?= $inst_search ?>
        </div>

        <div id="copymove-subfolder" style="display: none;">
            <label for="subfolder"><?= _('Ordner') ?></label>
            <select id="subfolder" name="user_dest_folder" ></select>
        </div>

        <div data-dialog-button>
        <? if ($copy_mode): ?>
            <?= Studip\Button::createAccept(_('Kopieren'), 'form_sent') ?>
        <? else: ?>
            <?= Studip\Button::createAccept(_('Verschieben'), 'form_sent') ?>
        <? endif ?>
            <?= Studip\LinkButton::createCancel(
                _('Abbrechen'),
                $controller->url_for('/goto/' . $parent_folder_id)
            ) ?>
        </div>
    </form>
</div>

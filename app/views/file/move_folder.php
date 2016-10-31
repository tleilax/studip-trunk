<form class="default" 
    <? if($copy_mode): ?>
    action="<?= $controller->url_for('/copy/'. $folder_id); ?>" 
    <? else: ?>
    action="<?= $controller->url_for('/move/'. $folder_id); ?>" 
    <? endif ?>
    data-dialog="reload-on-close">
    <div id="copymove-destination">
        <label for="destination"><?= _('Ziel'); ?></label>
        <select id="destination">
            <optgroup label="lokal">
                <option value="myfiles"><?= _('Meine Dateien'); ?></option>
                <option value="courses"><?= _('Veranstaltungen'); ?></option>
                <option value="institutes"><?= _('Einrichtungen'); ?></option>
            </optgroup>
            <optgroup label="extern">
                <option disabled="disabled" value="plugin1"><?= _('Plugin1'); ?></option>
                <option disabled="disabled" value="plugin2"><?= _('Plugin2'); ?></option>
                <option disabled="disabled" value="plugin3"><?= _('Plugin3'); ?></option>
            </optgroup>
        </select>
    </div>
    
    <div id="copymove-range" style="display: none;">
        <label for="range"><?= htmlReady($range_name); ?></label>
        <?= $search; ?>
    </div>
    
    <div id="copymove-range-inst" style="display: none;">
        <label for="range"><?= htmlReady($range_name); ?></label>
        <?= $inst_search; ?>
    </div>
    
    <div id="copymove-subfolder" style="display: none;">
        <label for="subfolder"><?= _('Ordner'); ?></label>
        <select id="subfolder" name="dest_folder" ></select>
    </div>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Verschieben'), 'form_sent') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>
</form>

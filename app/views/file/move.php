<form class="default" action="<?= $controller->url_for('/move/'. $file_ref); ?>">

	<input type="hidden" name="copymode" value="<?= $copymode; ?>">

	<div id="folder_select_-container">
    	<label for="folder_select_-destination"><?= _('Ziel'); ?></label>
    	<select id="folder_select_-destination"
            onchange="STUDIP.Files.changeFolderSource();">
    		<option value="null"></option>
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
	<input id="folder_select_-range-user_id" type="hidden" name="user_id" value="<?= htmlReady($user_id); ?>">
	<div id="folder_select_-range-course" style="display: none;">
    	<label for="range"><?= htmlReady(_('Veranstaltung')); ?></label>
    	<?= $search; ?>    	
	</div>
	<div id="folder_select_-range-inst" style="display: none;">
    	<label for="range"><?= htmlReady(_('Einrichtung')); ?></label>
    	<?= $inst_search; ?>
	</div>
	
	<div id="folder_select_-subfolder" style="display: none;">
    	<label for="subfolder"><?= _('Ordner'); ?></label>
    	<select id="subfolder" name="dest_folder" ></select>
	</div>


	<div data-dialog-button>
	<? if ($copymode == 'move'): ?>
            <?= Studip\Button::createAccept(_('Verschieben'), 'do_move') ?>
        <? else: ?>
            <?= Studip\Button::createAccept(_('Kopieren'), 'do_move') ?>
        <? endif ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>
</form>

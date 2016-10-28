<form class="default" action="<?= $controller->url_for('/move/'. $file_ref); ?>">

	<div id="copymove-destination">
    	<label for="destination"><?= _('Ziel'); ?></label>
    	<select id="destination">
        	<optgroup label="lokal">
        		<option value="myfiles"><?= _('Meine Dateien'); ?></option>
        		<option value="courses"><?= _('Veranstaltungen'); ?></option>
        		<option value="institutes"><?= _('Einrichtungen'); ?></option>
        	</optgroup>
        	<optgroup label="extern">
        		<option value="plugin1"><?= _('Plugin1'); ?></option>
        		<option value="plugin2"><?= _('Plugin2'); ?></option>
        		<option value="plugin3"><?= _('Plugin3'); ?></option>
        	</optgroup>
    	</select>
	</div>
	
	<div id="copymove-range" style="display: none;">
    	<label for="range"><?= htmlReady($range_name); ?></label>
    	<?= $search; ?>
	</div>
	
	<div id="copymove-subfolder" style="display: none;">
    	<label for="subfolder"><?= _('Ordner'); ?></label>
    	<select id="subfolder" name="dest_folder" ></select>
	</div>


	<div data-dialog-button>
        <?= Studip\Button::createAccept(_('Verschieben'), 'do_move') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>
</form>

<form class="default" action="<?= $controller->url_for('/move/'. $file_ref); ?>">

	<input type="hidden" name="copymode" value="<?= $move_copy; ?>">

	<div id="copymove-destination">
    	<label for="destination"><?= _('Ziel'); ?></label>
    	<select id="destination">
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
	<input id="copymove-range-user_id" type="hidden" name="user_id" value="<?= htmlReady($user_id); ?>">
	<div id="copymove-range-course" style="display: none;">
    	<label for="range"><?= htmlReady(_('Veranstaltung')); ?></label>
    	<?= $search; ?>    	
	</div>
	<div id="copymove-range-inst" style="display: none;">
    	<label for="range"><?= htmlReady(_('Einrichtung')); ?></label>
    	<?= $inst_search; ?>
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

<h3><?= $rule->getName() ?></h3>
<?= $tpl ?>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="limitedadmission_label">
        <label for="maxnumber"><?= _('Maximale Anzahl erlaubter Anmeldungen') ?>:</label>
    </div>
    <div class="admission_value">
        <input type="number" name="maxnumber" size="4" max="4" value="<?= $rule->getMaxNumber() ?>" required/>
    </div>
</div>
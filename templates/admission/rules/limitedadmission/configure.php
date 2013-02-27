<h3><?= $rule->getName() ?></h3>
<?php echo $this->render_partial('admission/rules/configure.php'); ?>
<div style="width: 95%; padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
    <div align="right" style="display: inline-block; vertical-align: top; width: 60%; font-weight: bold;">
        <label for="maxnumber"><?= _('Maximale Anzahl erlaubter Anmeldungen') ?>:</label>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <input type="number" name="maxnumber" size="4" max="4" value="<?= $rule->getMaxNumber() ?>" required/>
    </div>
</div>
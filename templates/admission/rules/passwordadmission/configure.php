<h3><?= $rule->getName() ?></h3>
<?php echo $this->render_partial('admission/rules/configure.php'); ?>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="passwordadmission_label">
        <label for="password1"><?= _('Zugangspasswort') ?>:</label>
    </div>
    <div class="admission_value">
        <input type="password" name="password1" size="25" max="40" value="<?= $rule->getPassword() ?>" required/>
    </div>
</div>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="passwordadmission_label">
        <label for="password2"><?= _('Passwort wiederholen') ?>:</label>
    </div>
    <div class="admission_value">
        <input type="password" name="password2" size="25" max="40" value="<?= $rule->getPassword() ?>" required/>
    </div>
</div>
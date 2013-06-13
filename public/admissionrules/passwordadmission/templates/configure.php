<h3><?= $rule->getName() ?></h3>
<?= $tpl ?>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="admissionrule_label">
        <label for="password1"><?= _('Zugangspasswort') ?>:</label>
    </div>
    <div class="admissionrule_value">
        <input type="password" name="password1" size="25" max="40" value="<?= $rule->getPassword() ?>" required/>
    </div>
</div>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="admissionrule_label">
        <label for="password2"><?= _('Passwort wiederholen') ?>:</label>
    </div>
    <div class="admissionrule_value">
        <input type="password" name="password2" size="25" max="40" value="<?= $rule->getPassword() ?>" required/>
    </div>
</div>
<h3><?= $rule->getName() ?></h3>
<?php echo $this->render_partial('admission/rules/configure.php'); ?>
<div style="width: 95%; padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
    <div align="right" style="display: inline-block; vertical-align: top; width: 40%; font-weight: bold;">
        <label for="password1"><?= _('Zugangspasswort') ?>:</label>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <input type="password" name="password1" size="25" max="40" value="<?= $rule->getPassword() ?>" required/>
    </div>
</div>
<div style="width: 95%; padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
    <div align="right" style="display: inline-block; vertical-align: top; width: 40%; font-weight: bold;">
        <label for="password2"><?= _('Passwort wiederholen') ?>:</label>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <input type="password" name="password2" size="25" max="40" value="<?= $rule->getPassword() ?>" required/>
    </div>
</div>
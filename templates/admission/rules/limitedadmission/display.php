<?php echo $this->render_partial('admission/rules/display.php'); ?>
<br/>
<?= sprintf(_('Es ist eine Anmeldung zu maximal %s Veranstaltungen m�glich.'),
    $rule->getMaxNumber()); ?>

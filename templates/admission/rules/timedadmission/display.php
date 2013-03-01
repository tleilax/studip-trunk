<?php echo $this->render_partial('admission/rules/display.php'); ?>
<br/>
<?php if ($rule->getStartTime() && !$rule->getEndTime()) { ?>
<?= sprintf(_("Die Anmeldung ist möglich ab %s."), date("d.m.Y, H:i", $rule->startTime)) ?>
<?php } else if (!$rule->getStartTime() && $rule->getEndTime()) { ?>
<?= sprintf(_("Die Anmeldung ist möglich bis %s."), date("d.m.Y, H:i", $rule->endTime)) ?>
<?php } else if ($rule->getStartTime() && $rule->getEndTime()) { ?>
<?= sprintf(_("Die Anmeldung ist möglich von %s bis %s."), date("d.m.Y, H:i", $rule->startTime), date("d.m.Y, H:i", $rule->endTime)); ?>
<?php
}
if ($rule->getDistributionTime()) {
?>
<?= sprintf(_("Die Platzverteilung erfolgt am %s um %s."), date("d.m.Y", $rule->distributionTime), date("H:i", $rule->distributionTime)); ?>
<?php } ?>
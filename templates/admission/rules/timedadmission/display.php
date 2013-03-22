<i><?= _('Anmeldezeitraum:') ?></i>
<br/>
<?php
$text = '';
if ($rule->getStartTime() && !$rule->getEndTime()) {
    $text = sprintf(_("ab %s"), date("d.m.Y, H:i", $rule->startTime));
} else if (!$rule->getStartTime() && $rule->getEndTime()) {
    $text = sprintf(_("bis %s"), date("d.m.Y, H:i", $rule->endTime));
} else if ($rule->getStartTime() && $rule->getEndTime()) {
    $text = sprintf(_("von %s bis %s"), date("d.m.Y, H:i", $rule->startTime), date("d.m.Y, H:i", $rule->endTime));
}
?>
<?= $text ?>
<?php
if ($rule->getDistributionTime()) {
?>
<i><?= _('Platzverteilung:') ?></i>
<?= date("d.m.Y", $rule->distributionTime).' '.date("H:i", $rule->distributionTime); ?>
<?php } ?>
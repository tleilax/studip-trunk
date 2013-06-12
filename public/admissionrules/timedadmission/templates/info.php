<?php
$text = _('');
if ($rule->getStartTime() && !$rule->getEndTime()) {
    $text = sprintf(_("Die Anmeldung ist m�glich ab %s."), date("d.m.Y, H:i", 
        $rule->startTime));
} else if (!$rule->getStartTime() && $rule->getEndTime()) {
    $text = sprintf(_("Die Anmeldung ist m�glich bis %s."), date("d.m.Y, H:i", 
        $rule->endTime));
} else if ($rule->getStartTime() && $rule->getEndTime()) {
    $text = sprintf(_("Die Anmeldung ist m�glich von %s bis %s."), 
        date("d.m.Y, H:i", $rule->startTime), date("d.m.Y, H:i", $rule->endTime));
}
?>
<?= $text ?>
<?php if ($rule->getDistributionTime()) { ?>
<br/>
<?= sprintf(_('Die Pl�tze in den betreffenden Veranstaltungen werden am %s '.
    'um %s verteilt.'), date("d.m.Y", $rule->distributionTime), 
    date("H:i", $rule->distributionTime)) ?>
<?php } ?>
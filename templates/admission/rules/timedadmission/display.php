<?php echo $this->render_partial('admission/rules/display.php'); ?>
<?php
// Start time but no end time given.
if ($rule->startTime && !$rule->endTime) {
    sprintf(_("Die Anmeldung ist möglich ab %s."), 
        date("d.m.Y, H:i", $rule->startTime))
// End time but no start time given.
} else if (!$rule->startTime && $rule->endTime) {
    sprintf(_("Die Anmeldung ist möglich bis %s."), 
        date("d.m.Y, H:i", $rule->endTime))
// Start and end time given.
} else if ($rule->startTime && $rule->endTime) {
    sprintf(_("Die Anmeldung ist möglich von %s bis %s."), 
        date("d.m.Y, H:i", $rule->startTime), 
        date("d.m.Y, H:i", $rule->endTime))
}
if ($rule->distributionTime) {
    sprintf(_("Die Platzverteilung erfolgt am %s um %s."), 
        date("d.m.Y", $rule->distributionTime),
        date("H:i", $rule->distributionTime))
}
?>
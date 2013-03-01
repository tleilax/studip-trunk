<?php
// Start time but no end time given.
if ($condition->startTime && !$condition->endTime) {
    $text = sprintf(_("gültig ab %s"), date("d.m.Y", $condition->startTime));
// End time but no start time given.
} else if (!$condition->startTime && $condition->endTime) {
    $text = sprintf(_("gültig bis %s"),
        date("d.m.Y", $condition->endTime));
// Start and end time given.
} else if ($condition->startTime && $condition->endTime) {
    $text = sprintf(_("gültig von %s bis %s"),
        date("d.m.Y", $condition->startTime), 
        date("d.m.Y", $condition->endTime));
}
?>
<?= $text ?>
<br/>
<?php
$i=0;
$fieldText = '';
foreach ($condition->getFields() as $field) {
    if ($i > 0) {
        $fieldText .= ' <b>'._('und').'</b> ';
    }
    $valueNames = $field->getValidValues();
    $fieldText .= $field->getName()." ".$field->getCompareOperator().
        " ".$valueNames[$field->getValue()];
    $i++;
    
}
echo $fieldText;
?>
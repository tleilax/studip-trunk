<i><?= _('Anmeldebedingungen') ?>:</i>
<br/>
<?php
$i = 0;
foreach ($rule->getConditions() as $condition) {
    if ($i > 0) {
        echo '<b>'._('oder').'</b><br/>';
    }
    echo $condition->toString().'<br/>';
    $i++;
}
?>
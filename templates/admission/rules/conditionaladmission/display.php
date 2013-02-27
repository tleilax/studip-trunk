<?php echo $this->render_partial('admission/rules/display.php'); ?>
<br/>
<?php
foreach ($rule->getConditions() as $condition) {
    echo $condition->toString();
}
?>
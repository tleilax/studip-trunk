<?php
$i=0;
$fieldText = '';
foreach ($filter->getFields() as $field) {
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
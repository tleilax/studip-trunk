<?php
# Lifter010: TODO
if (!isset($link)) $link = true;
echo $date->toString();
if ($date->getResourceId()) :
    echo ', '.  _("Ort:") .' ';
    echo implode(', ', getFormattedRooms(array($date->getResourceId() => '1'), $link));
endif ?>

<?php if (count($rule->getConditions()) == 1) { ?>
    <?= _('Folgende Bedingung muss zur Anmeldung erf�llt sein:') ?>
    <br/>
    <div id="conditions">
        <?php 
        $conditions = $rule->getConditions();
        $condition = reset($conditions);
        ?>
        <div id="condition_<?= $condition->getId() ?>">
            <i><?= $condition->toString() ?></i>
        </div>
    </div>
<?php } else { ?>
    <?= _('Mindestens eine der folgenden Bedingungen muss zur Anmeldung '.
        'erf�llt sein:') ?>
    <br/>
    <ul id="conditions">
        <?php
        $i = 0;
        foreach ($rule->getConditions() as $condition) {
        ?>
        <li id="condition_<?= $condition->getId() ?>">
            <i><?= $condition->toString() ?></i>
        </li>
        <?php
            $i++;
        }
        ?>
    </ul>
<?php } ?>
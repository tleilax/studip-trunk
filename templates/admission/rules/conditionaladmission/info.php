<?php if (count($rule->getConditions()) == 1) { ?>
    <?= _('Folgende Bedingung muss zur Anmeldung erfüllt sein:') ?>
    <br/>
    <div id="conditions">
        <?php 
        $conditions = $rule->getConditions();
        $condition = reset($conditions);
        ?>
        <div class="condition" id="condition_<?= $condition->getId() ?>">
            <i><?= $condition->toString() ?></i>
        </div>
    </div>
<?php } else { ?>
    <?= _('Es muss mindestens eine der folgenden Bedingungen zur Anmeldung '.
        'erfüllt sein:') ?>
    <br/>
    <ul id="conditions">
        <?php
        $i = 0;
        foreach ($rule->getConditions() as $condition) {
        ?>
        <li class="condition" id="condition_<?= $condition->getId() ?>">
            <i><?= $condition->toString() ?></i>
        </li>
        <?php
            $i++;
        }
        ?>
    </ul>
<?php } ?>
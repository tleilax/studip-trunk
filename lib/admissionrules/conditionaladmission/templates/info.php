<?php
if ($rule->getStartTime() && $rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt von %s bis %s.'), strftime('%d.%m.%Y %H:%M',
        $rule->getStartTime()), strftime('%d.%m.%Y %H:%M', $rule->getEndTime())).'<br/>';
} else if ($rule->getStartTime() && !$rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt ab %s.'), strftime('%d.%m.%Y %H:%M', $rule->getStartTime())).'<br/>';
} else if (!$rule->getStartTime() && $rule->getEndTime()) {
    echo sprintf(_('Diese Regel gilt bis %s.'), strftime('%d.%m.%Y %H:%M', $rule->getEndTime())).'<br/>';
}
?>
<?php if (count($rule->getUngroupedConditions()) == 1) { ?>
    <?= _('Folgende Bedingung muss zur Anmeldung erfüllt sein:') ?>
    <br/>
    <div id="conditions">
        <?php
        $conditions = $rule->getUngroupedConditions();
        $condition = reset($conditions);
        ?>
        <div id="condition_<?= $condition->getId() ?>">
            <i><?= $condition->toString() ?></i>
        </div>
    </div>
<?php } elseif (count($rule->getUngroupedConditions()) > 1) { ?>
    <?= _('Mindestens eine der folgenden Bedingungen muss zur Anmeldung '.
        'erfüllt sein:') ?>
    <br/>
    <ul id="conditions">
        <?php
        $i = 0;
        foreach ($rule->getUngroupedConditions() as $condition) {
        ?>
        <li id="condition_<?= $condition->getId() ?>">
            <i><?= $condition->toString() ?></i>
        </li>
        <?php
            $i++;
        }
        ?>
    </ul>
<?php } elseif (count($rule->getConditionGroups())) { ?>
    <?= _('Mindestens eine der folgenden Bedingungen muss zur Anmeldung '.
        'erfüllt sein:') ?>
    <br/>
    <ul id="conditions">
        <?php
        $i = 0;
        foreach ($rule->getConditiongroups() as $conditiongroup_id => $conditions) { 
        ?>
            <? if ($rule->conditiongroupsAllowed()) { ?>
            <li>
                <i><?= sprintf(_('Kontingent: %s Prozent'), $rule->getQuota($conditiongroup_id)) ?></i>
            </li>
            <? } ?>
        <ul id="conditiongroup">
            <?php 
            foreach ($conditions as $condition) { 
            ?>
                <li id="condition_<?= $condition->getId() ?>">
                    <i><?= $condition->toString() ?></i>
                </li>
            <?php 
                $i++;
            }
            ?>
            </ul>
        <?php
        }
        ?>
    </ul>
<?php }
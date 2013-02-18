<div class="rule" id="rule_<?= $rule->getId() ?>">
    <a href="#" onclick="return STUDIP.Admission.toggleDetails('rule_arrow_<?= $rule->getId() ?>', 'rule_details_<?= $rule->getId() ?>')">
        <?= Assets::img('icons/16/blue/arr_1right.png', 
            array('id' => 'rule_arrow_'.$rule->getId(), 
            'align' => 'top', 'rel' => Assets::image_path('icons/16/blue/arr_1down.png'))) ?>
        <?= $rule->getName() ?>
    </a>
    <a href="#" onclick="return STUDIP.Admission.removeRule('rule_<?= $rule->getId() ?>', 'rules')">
        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
    <div id="rule_details_<?= $rule->getId() ?>"<?= $via_ajax ? ' style="display:none"' : '' ?>>
        <?= nl2br(utf8_encode($rule->toString())) ?>
    </div>
    <input type="hidden" name="rules[]" value="<?= htmlentities(serialize($rule)) ?>"/>
</div>
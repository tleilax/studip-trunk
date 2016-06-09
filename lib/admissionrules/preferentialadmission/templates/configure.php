<h3><?= htmlReady($rule->getName()) ?></h3>
<label for="prefadmission_conditions" class="caption">
    <?= _('Folgende Personen bei der Platzverteilung bevorzugen:') ?>
</label>
<div id="prefadmission_conditions">
    <?php if (!$rule->getConditions()) : ?>
    <span class="nofilter">
        <i><?= _('Sie haben noch keine Auswahl festgelegt.'); ?></i>
    </span>
    <?php else : ?>
    <div class="userfilter">
        <?php foreach ($rule->getConditions() as $condition) :
            $condition->show_user_count = true; ?>
            <div class="condition" id="condition_<?= $condition->getId() ?>">
                <?= $condition->toString() ?>
                <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
                    class="conditionfield_delete">
                    <?= Icon::create('trash', 'clickable')->asImg(); ?></a>
                <input type="hidden" name="conditions[]" value="<?= htmlReady(serialize($condition)) ?>"/>
            </div>
        <?php endforeach ?>
    </div>
    <?php endif ?>
    <br><br>
    <a href="<?= URLHelper::getURL('dispatch.php/userfilter/filter/configure/prefadmission_conditions') ?>"
       onclick="return STUDIP.UserFilter.configureCondition('condition', '<?=
            URLHelper::getLink('dispatch.php/userfilter/filter/configure/prefadmission_conditions') ?>')">
        <?= Icon::create('add', 'clickable', ['title' => _('Bedingung hinzuf�gen')])->asImg(16, ["alt" => _('Bedingung hinzuf�gen')]) ?>
        <?= _('Bedingung hinzuf�gen') ?>
    </a>
</div>
<br>
<label class="caption">
    <input type="checkbox" name="favor_semester"<?= $rule->getFavorSemester() ? ' checked' : '' ?>/>
    <?= _('H�here Fachsemester bevorzugen') ?>
</label>

<?php
$topics = CourseTopic::findBySeminar_id($folder->range_id);
?>
<label>
    <?= _('Wählen sie eine zugehöriges Thema aus') ?>
    <select name="topic_id">
    <? if (count($topics) === 0): ?>
        <option value="">
            <?= _('Es existiert kein Thema in dieser Veranstaltung') ?>
        </option>
    <? endif; ?>
    <? foreach ($topics as $one_topic): ?>
        <option <?=(@$topic->id === $one_topic->id ? 'selected' : '')?> value="<?= htmlReady($one_topic->id) ?>">
            <?= htmlReady($one_topic->title) ?>
        </option>
    <? endforeach; ?>
    </select>
</label>

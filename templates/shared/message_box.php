<?
# Lifter010: TODO
?>
<div class="messagebox messagebox_<?= $class ?> <? if (sizeof($details) && $close_details): ?>details_hidden<? endif; ?>">
    <div class="messagebox_buttons">
    <? if (sizeof($details) && $close_details) : ?>
        <a class="details" href="#" title="<?=_('Detailanzeige umschalten')?>"><?=_('Detailanzeige umschalten')?></a>
    <? endif ?>
        <a class="close" href="#" title="<?=_('Nachrichtenbox schliessen')?>"><?=_('Nachrichtenbox schliessen')?></a>
    </div>
    <?= $message ?>
<? if (sizeof($details)) : ?>
    <div class="messagebox_details">
        <ul>
        <? foreach ($details as $li) : ?>
            <li><?= $li ?></li>
        <? endforeach ?>
        </ul>
    </div>
<? endif ?>
</div>

<?
# Lifter010: TODO
?>
<tr>
    <? if ($tpl['cycle_id']) : ?>
    <td width="1%" style="padding: 0; <?= !$tpl['last_element'] ? "background-image: url('assets/images/forumstrich.gif');" : '' ?>">
        <a name="<?=$tpl['sd_id']?>" />

        <? if ($tpl['last_element']) : ?>
        <?= Assets::img('forumstrich2.gif') ?>
        <? else : ?>
        <?= Assets::img('forumstrich3.gif') ?>
        <? endif ?>
    </td>
    <? endif ?>

    <td width="1%" align="right" valign="center" class="<?=$tpl['class']?>" nowrap="nowrap" style="padding: 0; height: 27px;">
        <? if (!$_LOCKED) : ?>
            <input type="checkbox" name="singledate[]" value="<?=$tpl['sd_id']?>" <?= $tpl['checked'] ? 'checked="checked"' : '' ?>>
        <? endif ?>
    </td>
    <td width="44%" nowrap class="<?=$tpl['class']?>" style="padding: 0;">
        <? if (!$_LOCKED) : ?>
        <a class="tree" href="<?= URLHelper::getLink('?cycle_id=' . $tpl['cycle_id'] . '&singleDateID='. $tpl['sd_id'] .'#'. $tpl['sd_id']) ?>">
        <? endif ?>
            <? if ($tpl['deleted']) : ?>
                <span style="color: #666666"><?= $tpl['date'] ?></span>
            <? else : ?>
                <?=$tpl['date']?>
            <? endif ?>
        <? if (!$_LOCKED) : ?>
        </a>
        <? endif ?>
    </td>
    <td width="30%" nowrap class="<?=$tpl['class']?>" style="padding: 0;">
        <? if ($tpl['deleted']) : ?>
            <? if ($tpl['comment']) : ?>
                <i><?=_("(fällt aus)")?> <?=tooltipIcon($tpl['comment'], false)?></i>
            <? else : ?>
            <span style="color: #666666">
                <?=$tpl['room']?>
            </span>
            <? endif ?>
        <? else : ?>
            <?=$tpl['room']?>
            <? if ($tpl['ausruf']) : ?>
                <a href="javascript:;" onClick="alert('<?=jsReady($tpl['ausruf'], 'inline-single')?>')">
                    <?= Assets::img($tpl['symbol'], array('title' => $tpl['ausruf']))?>
                </a>
            <? endif ?>
        <? endif; ?>
    </td>
    <td width="20%" nowrap class="<?=$tpl['class']?>" align="right" style="padding: 0;">
        <? if (!$_LOCKED) : ?>
            <a href="<?= URLHelper::getLink('?cycle_id=' . $tpl['cycle_id'] . '&singleDateID='. $tpl['sd_id'] .'#'. $tpl['sd_id']) ?>" style="margin-right: 10px">
                <?= Icon::create('edit', $tpl['deleted'] ? 'inactive' : 'clickable', ['title' => _("Termin bearbeiten")])->asImg(['class' => 'text-top']) ?>
            </a>

            <? if ($tpl['deleted']) : ?>
                <a href="<?= URLHelper::getLink('?cmd=undelete_singledate&sd_id='. $tpl['sd_id'] .'&cycle_id='. $tpl['cycle_id'] .'#'. $tpl['sd_id'])?>">
                    <?= Icon::create('trash+decline', 'inactive', ['title' => _("Termin wiederherstellen")])->asImg(['class' => 'text-top']) ?>
                </a>
            <? else : ?>
                <a href="<?= URLHelper::getLink('?cmd=delete_singledate&sd_id='. $tpl['sd_id'] .'&cycle_id='. $tpl['cycle_id'] .'#'. $tpl['sd_id'])?>">
                    <?= Icon::create('trash', 'clickable', ['title' => _("Termin löschen")])->asImg(['class' => 'text-top'])?>
                </a>
            <? endif ?>
        <? elseif(!$cancelled_dates_locked) : ?>
            <? if (!$tpl['deleted']) : ?>
                <a href="javascript:STUDIP.CancelDatesDialog.initialize('<?=UrlHelper::getScriptURL('dispatch.php/course/cancel_dates', array('termin_id' =>  $tpl['sd_id']))?>')">
                    <?= Icon::create('calendar-visible+visibility', 'clickable', ['title' => _("Termin ausfallen lassen")])->asImg(['class' => 'text-top'])?>
                </a>
            <? endif ?>
        <? endif ?>
    </td>
</tr>
<tr id="date_<?= $date->getId() ?>" class="<?= $date instanceof CourseExDate ? "ausfall" : "" ?><?= $is_next_date ? 'nextdate' : ""?>"<?= $is_next_date ? ' title="'._("Der n�chste Termin").'"' : ""?> data-termin_id="<?= htmlReady($date->id) ?>">
    <td data-timestamp="<?=htmlReady($date['date']);?>" class="date_name">
        <? $icon = 'date+' . ($date['chdate'] > $last_visitdate ? 'new/' : '');?>
        <? if (is_a($date, "CourseExDate")) : ?>
            <?= Icon::create($icon, 'info')->asImg(['class' => "text-bottom"]) ?>
            <?= htmlReady($date->getFullname()) ?>
            <?= tooltipIcon($date->content)?>
        <? else : ?>
        	<? if (!$show_raumzeit) {
        	    $dialog_url = URLHelper::getLink('dispatch.php/course/dates/singledate/' . $date->getId());
        	} else {
        	    $dialog_url = URLHelper::getLink('dispatch.php/course/dates/details/' . $date->getId());
        	} ?>
            <a href="<?= $dialog_url ?>" data-dialog="size=auto">
                <?= Icon::create($icon, 'clickable')->asImg(['class' => "text-bottom"]) ?>
                <?= htmlReady($date->getFullname()) ?>
            </a>
        <? endif ?>
        <? if (count($date->dozenten) && count($date->dozenten) != $lecturer_count) : ?>
            (<? foreach ($date->dozenten as $key => $dozent) {
                if ($key > 0) {
                    echo ", ";
                }
                echo htmlReady($dozent->getFullName());
            } ?>)
        <? endif ?>
    </td>
    <td class="responsive-hidden"><?= htmlReady($date->getTypeName()) ?></td>
    <? if (count($course->statusgruppen)) : ?>
        <td class="responsive-hidden">
            <? if (count($date->statusgruppen)) : ?>
                <ul class="clean">
                <? foreach ($date->statusgruppen as $statusgruppe) : ?>
                    <li><?= htmlReady($statusgruppe['name']) ?></li>
                <? endforeach ?>
                </ul>
            <? else : ?>
                <?= _("alle") ?>
            <? endif ?>
        </td>
    <? endif ?>
    <? if (!$date instanceof CourseExDate) : ?>
        <td class="responsive-hidden">
            <div style="display: flex; flex-direction: row;">
                <ul class="themen_list clean" style="">
                <? foreach ($date->topics as $topic) : ?>
                    <?= $this->render_partial('course/dates/_topic_li', compact('topic', 'date')) ?>
                <? endforeach ?>
                </ul>
                <? if ($GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) : ?>
                    <a href="<?= URLHelper::getLink("dispatch.php/course/dates/new_topic", array('termin_id' => $date->getId())) ?>" style="align-self: flex-end;" title="<?= _("Thema hinzuf�gen") ?>" data-dialog>
                        <?= Icon::create('add', 'clickable')->asImg(12) ?>
                    </a>
                <? endif ?>
            </div>
        </td>
        <td>
        <? if ($date->getRoom()) : ?>
            <?= $date->getRoom()->getFormattedLink() ?>
        <? else : ?>
            <?= htmlReady($date->raum) ?>
        <? endif ?>
        </td>
    <? else : ?>
        <td colspan="2"></td>
    <? endif ?>
</tr>
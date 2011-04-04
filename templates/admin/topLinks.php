<? if ($adminList) : ?>
    <? foreach ($adminList as $index => $seminar) {
        if ($next_one === "next_round") {
            $next_one = $index;
            break;
        }
        if ($seminar['Seminar_id'] === $course_id) {
            $next_one = "next_round";
        } else {
            $last_one = $index;
        }
    }
    if ($next_one === "next_round") {
        unset($next_one);
    }
    ?>
<div style="width: 70%; margin: 10px; margin-left: auto; margin-right: auto;text-align: center;" id="admin_top_links">
    <? if (isset($last_one)) : ?>
    <div style="float: left;">
        <a href="<?= URLHelper::getLink("?#admin_top_links", array('cid' => $adminList[$last_one]['Seminar_id'])) ?>" title="<?= htmlReady($adminList[$last_one]['Name']) ?>">
            <?= Assets::img("icons/16/blue/arr_1left.png", array('class' => "text-bottom")) ?>
            <?= _("zur�ck") ?>
        </a>
    </div>
    <? endif ?>
    <? if (isset($next_one)) : ?>
    <div style="float: right;">
        <a href="<?= URLHelper::getLink("?#admin_top_links", array('cid' => $adminList[$next_one]['Seminar_id'])) ?>" title="<?= htmlReady($adminList[$next_one]['Name']) ?>">
            <?= _("vor") ?>
            <?= Assets::img("icons/16/blue/arr_1right.png", array('class' => "text-bottom")) ?>
        </a>
    </div>
    <? endif ?>
    <div>
        <a href="<?= URLHelper::getLink("adminarea_start.php", array('list' => "TRUE")) ?>">
            <?= Assets::img("icons/16/blue/arr_1up.png", array('class' => "text-bottom")) ?>
            <?= _("Liste") ?>
        </a>
    </div>
</div>
<? endif ?>
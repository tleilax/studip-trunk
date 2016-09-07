<?php
// in den Controller
$room_request_filter = function ($date) {
    return $date->room_request && !$date->room_request->isNew() && $date->room_request->closed < 2;
};
?>

<section class="contentbox timesrooms">
    <header>
        <h1>
            <?= _('Unregelm��ige Termine / Blocktermine') ?>
        </h1>
        <? if(!$locked) : ?>
            <? $actionMenu = ActionMenu::get()?>
            <? $actionMenu->addLink(
                    $controller->link_for('course/timesrooms/createSingleDate/' . $course->id, $linkAttributes),
                    _('Einzeltermin hinzuf�gen'),
                    Icon::create('date+add', 'clickable', ['title' => _('Einzeltermin hinzuf�gen')]),
                    ['data-dialog' => 'size=600'])
            ?>

            <? $actionMenu->addLink(
                    $controller->url_for('course/block_appointments/index/' . $course->id, $linkAttributes),
                    _('Blocktermin hinzuf�gen'),
                    Icon::create('timetable+add', 'clickable', ['title' => _('Blocktermin hinzuf�gen')]),
                    ['data-dialog' => 'size=600'])
            ?>
            <?= $actionMenu->render() ?>
        <? endif ?>
    </header>
<? if (!empty($single_dates)): ?>
    <form class="default collapsable" action="<?= $controller->url_for('course/timesrooms/stack', $linkAttributes) ?>"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?> method="post">
        <?= CSRFProtection::tokenTag() ?>
        <? foreach ($single_dates as $semester_id => $termine) : ?>
        <article id="singledate-<?= $semester_id ?>" class="<?= count($single_dates) === 1 ? 'open' :  ContentBoxHelper::classes('singledate-' . $semester_id) ?>">
            <header>
                <h1>
                    <? if(!$locked) : ?>
                        <input type="checkbox" class="date-proxy"
                               data-proxyfor="#singledate-<?= $semester_id ?> .ids-irregular"
                               data-activates=".actionForAllIrregular">
                    <? endif ?>
                    <a href="<?= ContentBoxHelper::href('singledate-' . $semester_id) ?>">
                        <?= htmlReady(Semester::find($semester_id)->name) ?>
                    </a>
                </h1>
                <nav>
                    <span>
                        <?= sprintf(ngettext('%u Termin', '%u Termine', count($termine)),
                                     count($termine)) ?>
                    </span>
                    <?php if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) : ?>
                        <span>
                            <?= _('Einzel-Raumanfrage') ?>:
                            <? if (($rr_count = count($termine->filter($room_request_filter))) > 0): ?>
                                <?= sprintf(_('%u noch offen'), $rr_count) ?>
                            <? else: ?>
                                <?= _('keine offen') ?>
                            <? endif; ?>
                        </span>
                    <?php endif ?>
                </nav>
            </header>
            <section>
                <table class="default nohover">
                    <colgroup>
                        <? if (!$locked) :?>
                            <col width="30px">
                        <? endif ?>
                        <col width="30%">
                        <col>
                        <col width="20%">
                        <col width="50px">
                    </colgroup>

                    <tbody>
                    <? foreach ($termine as $termin): ?>
                        <?= $this->render_partial('course/timesrooms/_cycleRow.php', array(
                                'termin'    => $termin,
                                'class_ids' => 'ids-irregular',
                        )) ?>
                    <? endforeach; ?>
                    </tbody>
                </table>
            </section>
        </article>
    <? endforeach; ?>
    <? if(!$locked) : ?>
        <table class="default nohover">
            <colgroup>
                <col width="30px">
                <col width="30%">
                <col>
                <col width="20%">
                <col width="50px">
            </colgroup>

            <tfoot>
                <tr>
                    <td colspan="2">
                        <label class="horizontal">
                            <input type="checkbox" data-proxyfor=".date-proxy"
                                   data-activates=".actionForAllIrregular">
                            <?= _('Alle ausw�hlen') ?>
                        </label>
                    </td>
                    <td colspan="3" class="actions">
                        <select name="method" class="datesBulkActions actionForAllIrregular">
                            <?= $this->render_partial('course/timesrooms/_stack_actions.php') ?>
                        </select>
                        <?= Studip\Button::create(_('Ausf�hren'), 'run', array(
                                'class' => 'actionForAllIrregular',
                                'data-dialog' => 'size=big',
                        )) ?>
                    </td>
                </tr>
            </tfoot>
            <? endif ?>
        </table>
    </form>
<? else: ?>
    <section>
        <p class="text-center">
            <strong>
                <?= _('Keine unregelm��igen Termine vorhanden') ?>
            </strong>
        </p>
    </section>
<? endif; ?>
</section>

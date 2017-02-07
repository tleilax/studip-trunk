<section class="contentbox timesrooms">
    <header>
        <h1>
            <?= _('Regelm��ige Termine') ?>
        </h1>
        <? if(!$locked) : ?>
            <nav>
                <a class="link-add"
                   href="<?= $controller->link_for('course/timesrooms/createCycle', $linkAttributes) ?>"
                   data-dialog="size=600"
                   title="<?= _('Regelm��igen Termin hinzuf�gen') ?>">
                    <?= _('Regelm��igen Termin hinzuf�gen') ?>
                </a>
            </nav>
        <? endif ?>
    </header>

<? if (!empty($cycle_dates)) : ?>
    <? foreach ($cycle_dates as $metadate_id => $cycle) : ?>

        <form class="default collapsable" action="<?= $controller->url_for('course/timesrooms/stack/' . $metadate_id, $linkAttributes) ?>"
              method="post" <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?>>
            <?= CSRFProtection::tokenTag() ?>

            <article id="<?= $metadate_id ?>" class="<?= ContentBoxHelper::classes($metadate_id) ?>">
                <header class="<?= $course->getCycleColorClass($metadate_id) ?>">
                    <h1>
                    <? if ($info = $course->getBookedRoomsTooltip($metadate_id)) : ?>
                        <?= tooltipIcon($info); ?>
                    <? elseif ($course->getCycleColorClass($metadate_id) === 'red'): ?>
                        <?= tooltipIcon(_('Keine Raumbuchungen vorhanden')) ?>
                    <? else: ?>
                        <?= tooltipIcon(_('Keine offenen Raumbuchungen')) ?>
                    <? endif; ?>
                        <a href="<?= ContentBoxHelper::href($metadate_id) ?>">
                            <?= htmlReady($cycle['cycle']->toString('long')) ?>
                        </a>
                    </h1>
                    <section>
                        <? if (!$locked) : ?>
                            <span>
                                <strong><?= _('Raum') ?></strong>:
                            <? if (count($cycle['room_request']) > 0): ?>
                                <?= htmlReady(array_pop($cycle['room_request'])->name)?>
                            <? else : ?>
                                <?= _('keiner') ?>
                            <? endif; ?>
                            </span>
                            <?php if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) : ?>
                                <span>
                                    <strong><?= _('Einzel-Raumanfrage') ?></strong>:
                                    <?= htmlReady($course->getRequestsInfo($metadate_id)) ?>
                                </span>
                            <?php endif ?>

                    </section>
                    <? $actionMenu = ActionMenu::get()?>
                    <? $actionMenu->addLink(
                            $controller->url_for('course/timesrooms/createCycle/' . $metadate_id, $linkAttributes),
                            _('Diesen Zeitraum bearbeiten'),
                            Icon::create('edit', 'clickable', ['title' => _('Diesen Zeitraum bearbeiten')]),
                            ['data-dialog' => 'size=600'])
                    ?>
                    <? $actionMenu->addButton(
                            'delete_cycle',
                            _('Diesen Zeitraum l�schen'),
                            Icon::create('trash', 'clickable',
                                    ['title'        => _('Diesen Zeitraum l�schen'),
                                     'formaction'   => $controller->url_for('course/timesrooms/deleteCycle/' . $metadate_id, $linkAttributes),
                                     'data-confirm' => _('Soll dieser Zeitraum wirklich gel�scht werden?'),
                                     'style'        => 'margin: 0px']))
                    ?>
                    <?= $actionMenu->render() ?>
                    <? endif ?>
                </header>
                <section>
                    <table class="default nohover">
                        <colgroup>
                            <? if (!$locked) : ?>
                                <col width="30px">
                            <? endif ?>
                            <col width="30%">
                            <col>
                            <col width="20%">
                            <col width="50px">
                        </colgroup>
                    <? foreach ($cycle['dates'] as $semester_id => $termine) : ?>
                        <thead>
                            <tr>
                                <th colspan="<?= !$locked ? 5 : 4?>"><?= htmlReady(Semester::find($semester_id)->name) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <? foreach ($termine as $termin) : ?>
                            <?= $this->render_partial('course/timesrooms/_cycleRow.php',
                                    array('termin' => $termin,'class_ids' => 'ids-regular_' . $metadate_id)) ?>
                        <? endforeach ?>
                        </tbody>
                    <? endforeach ?>
                        <? if(!$locked) : ?>
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <label>
                                            <input type="checkbox"
                                                    data-proxyfor=".ids-regular_<?=$metadate_id?>"
                                                    data-activates=".actionForAllRegular_<?= $metadate_id ?>">
                                            <?= _('Alle ausw�hlen') ?>
                                        </label>
                                    </td>
                                    <td colspan="3" class="actions">
                                        <select name="method" class="datesBulkActions actionForAllRegular_<?= $metadate_id ?>">
                                            <?= $this->render_partial('course/timesrooms/_stack_actions.php') ?>
                                        </select>
                                        <?= Studip\Button::create(_('Ausf�hren'), 'run', array(
                                            'class' => sprintf('actionForAllRegular_%s', $metadate_id),
                                            'data-dialog' => 'size=big',
                                            )) ?>
                                    </td>
                                </tr>
                            </tfoot>
                        <? endif ?>
                    </table>

                </section>
            </article>
        </form>
    <? endforeach; ?>

<? else: ?>
    <section>
        <p class="text-center">
            <strong><?= _('Keine regelm��igen Termine vorhanden') ?></strong>
        </p>
    </section>
<? endif; ?>
</section>

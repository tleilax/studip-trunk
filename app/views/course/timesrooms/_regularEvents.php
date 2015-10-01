<section class="contentbox">
    <header>
        <h1>
            <?= _('Regelmäßige Termine') ?>
        </h1>
        <nav>
            <a data-dialog-button class="link-add"
               href="<?= $controller->link_for('course/timesrooms/createCycle', $editParams) ?>"
               data-dialog="size=big"
               title="<?= _('Regelmäßigen Termin hinzufügen') ?>">
                <?= _('Regelmäßigen Termin hinzufügen') ?>
            </a>
        </nav>
    </header>

    <? if (!empty($cycle_dates)) : ?>
        <?= CSRFProtection::tokenTag() ?>
        
        <? foreach ($cycle_dates as $metadate_id => $cycle) : ?>
            <? $roomRequest = array(); ?>
            <? foreach ($cycle['dates'] as $dates) : ?>
                <? foreach ($dates as $date) : ?>
                    <? $date_room = $date->getRoom() ?>
                    <? if (isset($date_room)) : ?>
                        <? $roomRequest[$date_room->id] = $date_room; ?>
                    <? endif; ?>
                <? endforeach; ?>
            <? endforeach; ?>
            <? if (empty($roomRequest)) : ?>
                <? $roomRequest = _('keiner'); ?>
            <? endif ?>


            <form class="studip-form" action="<?= $controller->url_for('course/timesrooms/stack/' . $metadate_id, $editParams) ?>"
                  method="post" <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?>>
            <article id="<?= $metadate_id ?>"
                     class="<?= Request::get('contentbox_open') == $metadate_id ? 'open' : '' ?>">
                        
                        <!--TODO alternative für Seminar::getInstance-->
                        <? $tmp_course = Seminar::GetInstance($course->id)?>
                        <header <?= ($class = $tmp_course->getCycleColorClass($metadate_id)) ? 'class="' . $class . '"' : '' ?>>
                    <h1>
                        <a href="<?= ContentBoxHelper::href($metadate_id, array()) ?>">
                            <?= htmlReady($cycle['cycle']->toString('long')) ?>
                        </a>
                    </h1>
                    <nav>
                        <!--TODO alternative für Seminar::getInstance-->
                        <? if ($info = $tmp_course->getBookedRoomsTooltip($metadate_id)) : ?>
                            <?= tooltipIcon($info, true); ?>
                        <? endif ?>
                        <span>
                            <?= _('Raum') ?>: 
                            <? if(is_array($roomRequest) ) : ?>
                                <?= htmlReady(array_pop($roomRequest)->name)?>
                            <? else : ?>
                                <?= $roomRequest ?>
                            <? endif; ?>
                        </span>
                        <span>
                            <!--TODO alternative für Seminar::getInstance-->
                            <?= _('Einzel-Raumanfrage') ?>: <?= htmlReady($tmp_course->getRequestsInfo($metadate_id)) ?>
                        </span>
                        <span>
                            <a href="<?= $controller->url_for('course/timesrooms/createCycle/' . $metadate_id) ?>"
                               data-dialog="size=big">
                                <?= Assets::img('icons/blue/edit', tooltip2(_('Diesen Zeitraum bearbeiten'))) ?>
                            </a>
                            <?= Assets::input('icons/blue/trash', tooltip2(_('Diesen Zeitraum löschen')) + array(
                                    'formaction'   => $controller->url_for('course/timesrooms/deleteCycle/' . $metadate_id),
                                    'data-dialog'  => 'size=big',
                                    'data-confirm' => _('Soll dieser Zeitraum wirklich gelöscht werden?'),
                                )) ?>
                        </span>
                        </nav>
                    </header>
                    <section>
                        <? $dates = $cycle['dates'] ?>

                        <table class="default nohover">
                            <colgroup>
                                <col width="30px">
                                <col width="30%">
                                <col>
                                <col width="20%">
                                <col width="50px">
                            </colgroup>
                            <? foreach ($dates as $semester_id => $termine) : ?>
                                
                            <thead>
                                <tr>
                                    <th colspan="5"><?= htmlReady(Semester::find($semester_id)->name) ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <? foreach ($termine as $termin) : ?>
                                    <?= $this->render_partial('course/timesrooms/_cycleRow.php', 
                                            array('termin' => $termin,'class_ids' => 'ids-regular')) ?>
                                <? endforeach ?>
                                </tbody>
                            <? endforeach ?>
                            <tfoot>
                                
                            <tr>
                                <td colspan="2">
                                    <section style="margin: 0; padding: 0">
                                        <input data-proxyfor=".ids-regular" type="checkbox"
                                               id="checkAllRegular" data-activates=".actionForAllRegular">
                                        <label for="checkAllRegular" class="horizontal">
                                            <?= _('alle Auswählen') ?>
                                        </label>
                                    </section>
                                </td>
                                <td colspan="3" class="action">
                                    <select name="method" class="actionForAllRegular">
                                        <?= $this->render_partial('course/timesrooms/_stack_actions.php') ?>
                                    </select>
                                    <?= Studip\Button::create('ausführen', 'run', array('class' => 'actionForAllRegular','data-dialog' => 'size=big')) ?>
                                </td>
                            </tr>
                            </tfoot>
                        </table>

                    </section>
                </article>
            </form>
        <? endforeach; ?>

    <? else : ?>
        <section>
            <p class="text-center">
                <strong><?= sprintf(_('Keine regelmäßige Termine für %s vorhanden'), htmlReady($course->name)) ?></strong>
            </p>
        </section>
    <? endif; ?>
</section>

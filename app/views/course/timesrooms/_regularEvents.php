<section class="contentbox">
    <header>
        <h1>
            <?= _('Regelmäßige Termine') ?>
        </h1>
        <nav>
            <a data-dialog-button class="link-add" href="<?= $controller->link_for('course/timesrooms/editCycle') ?>" data-dialog
               title="<?= _('Regelmäßigen Termin hinzufügen') ?>">
                <?= _('Neuen Zeitraum festlegen') ?>
            </a>
        </nav>
    </header>

    <? if (!empty($cycle_dates)) : ?>

        <? foreach ($cycle_dates as $metadate_id => $cycle) : ?>
            <? if (!$roomRequest = $course->getDatesTemplate('dates/seminar_predominant_html', array('cycle_id' => $metadate_id))) : ?>
                <? $noRequest = true ?>
                <? $roomRequest = _('keiner'); ?>
            <? endif ?>
            <article id="<?= $metadate_id ?>"
                     class="<?= Request::get('contentbox_open') == $metadate_id ? 'open' : '' ?>">
                <header class="<?= $noRequest ? 'red' : 'green'?>">
                    <h1>
                        <a href="<?= ContentBoxHelper::href($metadate_id, array()) ?>">
                            <?= htmlReady($cycle['name']) ?>
                        </a>
                    </h1>
                    <nav>
                            <span>
                                <?= sprintf(_('Raum %s'), ': ' . $roomRequest) ?>
                            </span>
                            <span>
                                <?= sprintf(_('Einzel-Raumanfrage %s'), $room = RoomRequest::existsByCycle($metadate_id) ? $room : _('nein'))
                                ?>
                            </span>
                    </nav>
                </header>

                <section>
                    <? $dates = $cycle['dates'] ?>
                    <table class="default nohover">
                        <colgroup>
                            <col width="30px">
                            <col>
                            <col width="30%">
                            <col width="10%%">
                        </colgroup>
                        <? foreach ($dates as $semester_id => $termine) : ?>
                            <thead>
                            <tr>
                                <th colspan="4"><?= htmlReady(Semester::find($semester_id)->name) ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <? foreach ($termine as $termin) : ?>
                                <?= $this->render_partial('course/timesrooms/_cycleRow.php', array('termin'    => $termin,
                                                                                                   'class_ids' => 'ids-regular'
                                )) ?>
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
                            <td>
                                <select name="actionForAllRegular" class="actionForAllRegular">
                                    <option><?= _('aktion für alle ausgewählten') ?></option>
                                </select>
                            </td>
                            <td>
                                <?= Studip\Button::create('ausführen', 'run', array('class' => 'actionForAllIrregular')) ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </section>
            </article>
        <? endforeach; ?>

    <? else : ?>
        <section>
            <p class="text-center">
                <strong><?= sprintf(_('Kein Blocktermin für %s vorhanden'), htmlReady($course->name)) ?></strong>
            </p>
        </section>
    <? endif; ?>
</section>

<section class="contentbox">
    <header>
        <h1>
            <?= _('Regelmäßige Termine') ?>
        </h1>
        <nav>
            <a class="link-add" href="<?= $controller->link_for('course/timesrooms/editCycle') ?>" data-dialog title="<?= _('Regelmäßigen Termin hinzufügen') ?>">
                <?= _('Neuer Zeitraum') ?>
            </a>
        </nav>
    </header>

    <? if (empty($cycles)) : ?>
        <form class="studip-form">
            <? foreach ($cycles as $metadate_id => $cycle) : ?>
                <? if (!$roomRequest = $course->getDatesTemplate('dates/seminar_predominant_html', array('cycle_id' => $metadate_id))) : ?>
                    <? $noRequest = true ?>
                    <? $roomRequest = _('keiner'); ?>
                <? endif ?>
                <article id="<?= $metadate_id ?>"
                         class="<?= Request::get('contentbox_open') == $metadate_id ? 'open' : '' ?>">
                    <header>
                        <h1>
                            <a href="<?= ContentBoxHelper::href($metadate_id, array()) ?>">
                                <?= htmlReady($cycle->toString('long')) ?>
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
                        <? $termine = $course->getSingleDatesForCycle($metadate_id); ?>
                        <? if (!empty($termine)) : ?>
                            <table class="default">
                                <colgroup>
                                    <col width="30px">
                                    <col>
                                    <col width="30%">
                                    <col width="10%%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th colspan="2"><?= _('Termin') ?></th>
                                    <th><?= _('Raum') ?></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <? foreach ($termine as $termin) : ?>
                                    <?= $this->render_partial('course/timesrooms/_cycleRow.php', array('termin' => $termin, 'class_ids' => 'ids-regular')) ?>
                                <? endforeach; ?>
                                </tbody>
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
                                        <?= Studip\Button::create('ausführen', 'run', array('class' => 'actionForAllRegular')) ?>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        <? else : ?>
                            <br>
                            <?= MessageBox::info(sprintf(_('Keine Termine für %s vorhanden'), htmlReady($cycle->toString('short')))) ?>
                        <? endif; ?>
                    </section>
                </article>
            <? endforeach; ?>
        </form>
    <? else : ?>
        <section>
            <p class="text-center">
                <strong><?= sprintf(_('Kein Blocktermin für %s vorhanden'), htmlReady($course->name)) ?></strong>
            </p>
        </section>
    <? endif; ?>
</section>

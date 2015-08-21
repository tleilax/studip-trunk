<form class="studip-form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Regelmäßige Termine') ?>
            </h1>
            <nav>
                <span>
                    <a class="link-add" href="<?= $controller->link_for('course/timesrooms/editCycle') ?>" data-dialog title="<?=_('Regelmäßigen Termin hinzufügen')?>">
                        <?= _('Neuer Zeitraum') ?>
                    </a>
                </span>
            </nav>
        </header>

        <? if (!empty($cycles)) : ?>
            <? foreach ($cycles as $metadate_id => $cycle) : ?>
                <? if (!$roomRequest = $course->getDatesTemplate('dates/seminar_predominant_html', array('cycle_id' => $metadate_id))) : ?>
                    <? $noRequest = true ?>
                    <? $roomRequest = _('keiner'); ?>
                <? endif ?>
                <article id="<?= $metadate_id ?>"
                         class="<?= $noRequest ? 'red' : '' ?> <?= Request::get('contentbox_open') == $metadate_id ? 'open' : '' ?>">
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
                                    <th><?= _('Aktion') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <? foreach ($termine as $termin) : ?>
                                    <?= $this->render_partial('course/timesrooms/_cycleRow.php', array('termin' => $termin)) ?>
                                <? endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <section style="margin: 0; padding: 0">
                                            <input data-proxyfor="[name^=cycle_ids]:checkbox" type="checkbox"
                                                   id="checkAll">
                                            <label for="checkAll" class="horizontal">
                                                <?= _('alle Auswählen') ?>
                                            </label>
                                        </section>
                                    </td>
                                    <td>
                                        <select name="actionForAll">
                                            <option><?= _('aktion für alle ausgewählten') ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <?= Studip\Button::create('ausführen') ?>
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
        <? else : ?>
            <?= MessageBox::info(sprintf(_('Kein Blocktermin für %s vorhanden'), htmlReady($course->name))) ?>
        <? endif; ?>
    </section>
</form>
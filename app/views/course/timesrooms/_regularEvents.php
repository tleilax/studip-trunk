<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Regelm��ige Termine') ?>
            </h1>
            <nav>
                <span>
                    <a href="<?= $controller->link_for('course/timesrooms/editCycle') ?>" data-dialog>
                        <?= Assets::img('icons/16/blue/add.png', array('style' => 'float:right; margin-right:20px;',
                                                                       'title' => _('Regelm��igen Termin hinzuf�gen')))
                        ?>
                        <?= _('Neuer Zeitraum') ?>
                    </a>
                </span>
            </nav>
        </header>

        <? if (!empty($cycles)) : ?>
            <? foreach ($cycles as $metadate_id => $cycle) : ?>
                <? if (!$roomRequest = $course->getDatesTemplate('dates/seminar_predominant_html', array('cycle_id' => $metadate_id))) :?>
                    <? $roomRequest = _('keiner');?>
                <? endif ?>
                <article id="<?= $metadate_id ?>">
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
                                        <th colspan="2"><?=_('Termin')?></th>
                                        <th><?=_('Raum')?></th>
                                        <th><?=_('Aktion')?></th>
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
                                        <label for="checkAll">
                                            <input data-proxyfor="[name^=cycle_ids]:checkbox" type="checkbox" id="checkAll" data-prox>
                                            <?= _('alle Ausw�hlen') ?>
                                        </label>
                                    </td>
                                    <td>
                                        <select name="actionForAll">
                                            <option><?= _('aktion f�r alle ausgew�hlten') ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <?= Studip\Button::create('ausf�hren') ?>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        <? else : ?>
                            <br>
                            <?= MessageBox::info(sprintf(_('Keine Termine f�r %s vorhanden'), htmlReady($cycle->toString('short')))) ?>
                        <? endif; ?>
                    </section>
                </article>
            <? endforeach; ?>
        <? else : ?>
            <?= MessageBox::info(sprintf(_('Kein Blocktermin f�r %s vorhanden'), htmlReady($course->name))) ?>
        <? endif; ?>
    </section>
</form>
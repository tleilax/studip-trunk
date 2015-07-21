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
                <article id="<?= $metadate_id ?>">
                    <header>
                        <h1>
                            <a href="<?= ContentBoxHelper::href($metadate_id, array()) ?>">
                                <?= htmlReady($cycle->toString('long')) ?>
                            </a>
                        </h1>
                        <nav>
                            <? if (!$roomRequest = $course->getDatesTemplate('dates/seminar_predominant_html', array('cycle_id' => $metadate_id))) {
                                $roomRequest = _('keiner');
                            }?>
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
                                    <? foreach ($termine as $termin) : ?>
                                        <? //var_dump($termine)?><? //die;?>
                                        <?= $this->render_partial('course/timesrooms/_cycleRow.php', array('termin' => $termin)) ?>
                                    <? endforeach; ?>
                                    <tr>
                                    <td>
                                        <label for="checkAll">
                                            <input type="checkbox" id="checkAll">
                                            <?= _('alle Ausw�hlen') ?>
                                        </label>
                                    </td>
                                    <td>
                                        <select name="actionForAll">
                                            <option>aktion f�r alle ausgew�hlten</option>
                                        </select>
                                    </td>
                                    <td>
                                        <?= Studip\Button::create('ausf�hren') ?>
                                    </td>
                                </tr>    
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
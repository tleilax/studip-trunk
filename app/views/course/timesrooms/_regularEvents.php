<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Regelmäßige Termine') ?>
            </h1>
            <nav>
                <span>
                    <a href="">
                        <?= Assets::img('icons/16/blue/add.png', array('style' => 'float:right; margin-right:20px;',
                            'title' => _('Regelmäßigen Termin hinzufügen'))) ?>
                            <?=_('Neuer Blocktermin')?>
                    </a>
                </span>
            </nav>
        </header>

        <? if (!empty($cycles)) : ?>
                <? foreach ($cycles as $metadate_id => $cycle) : ?>
                    <article id="<?=$metadate_id?>">
                    <header>
                        <h1>
                            <a href="<?= ContentBoxHelper::href($metadate_id, array()) ?>">
                            <?= htmlReady($cycle->toString('long')) ?>
                            </a>
                        </h1>
                        <nav>
                        <!--TODO layout-->
                        <?
                        if (!$roomRequest = $course->getDatesTemplate('dates/seminar_predominant_html', array('cycle_id' => $metadate_id))) {
                            $roomRequest = _('keiner');
                        }
                        ?>
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
                                    <?= $this->render_partial('course/timesrooms/_cycleRow.php', array('termin' => $termin)) ?>
                            <? endforeach; ?>
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
        </article>
    </section>
</form>
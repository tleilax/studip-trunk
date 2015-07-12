<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Unregelm��ige Termine / Blocktermine') ?>
            </h1>
            <nav>
                <span>
                    <a href="">
                        <?=_('Neuen Einzeltermin')?>    
                        <?= Assets::img('icons/16/blue/add.png', array('style' => 'margin-right:20px;',
                            'title' => _('Einzeltermin hinzuf�gen')))?>
                    </a>
                </span>
                <span>
                    <a href="">
                        <?=_('Neuen Blocktermin')?>
                        <?= Assets::img('icons/16/blue/add.png', array('style' => 'margin-right:20px;',
                            'title' => _('Blocktermin hinzuf�gen')))?>
                    </a>
                </span>
            </nav>
        </header>
        <section>
            <? $termine = $course->getSingleDates(true, true, true) ?>
            <? if (!empty($termine)) : ?>
                <table class="default">
                    <? foreach ($termine as $termin) : ?>
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
                <?= MessageBox::info(sprintf(_('Keine unregelm��igen Termine f�r %s vorhanden'), $course->name)) ?>
            <? endif; ?>
        </section>
    </section>
</form>
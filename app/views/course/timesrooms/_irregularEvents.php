<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Unregelmäßige Termine / Blocktermine') ?>
            </h1>
            <nav>
                <span>
                    <a href="">
                        <?=_('Neuen Einzeltermin')?>    
                        <?= Assets::img('icons/16/blue/add.png', array('style' => 'margin-right:20px;',
                            'title' => _('Einzeltermin hinzufügen')))?>
                    </a>
                </span>
                <span>
                    <a href="">
                        <?=_('Neuen Blocktermin')?>
                        <?= Assets::img('icons/16/blue/add.png', array('style' => 'margin-right:20px;',
                            'title' => _('Blocktermin hinzufügen')))?>
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
                                <?= _('alle Auswählen') ?>
                            </label>
                        </td>
                        <td>
                            <select name="actionForAll">
                                <option>aktion für alle ausgewählten</option>
                            </select>
                        </td>
                        <td>
                            <?= Studip\Button::create('ausführen') ?>
                        </td>
                    </tr>
                </table>
            <? else : ?>
                <?= MessageBox::info(sprintf(_('Keine unregelmäßigen Termine für %s vorhanden'), $course->name)) ?>
            <? endif; ?>
        </section>
    </section>
</form>
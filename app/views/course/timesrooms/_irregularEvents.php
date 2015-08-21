<form class="studip-form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Unregelmäßige Termine / Blocktermine') ?>
            </h1>
            <nav>
                <span>
                    <a href="<?= $controller->link_for('course/timesrooms/editIrregular/0') ?>" data-dialog>
                        <?= _('Neuen Einzeltermin') ?>
                        <?= Assets::img('icons/16/blue/add.png', array('style' => 'margin-right:20px;',
                                                                       'title' => _('Einzeltermin hinzufügen')
                        )) ?>
                    </a>
                </span>
                <span>
                    <a href="<?= $controller->link_for('course/timesrooms/editBlock/0') ?>" data-dialog>
                        <?= _('Neuen Blocktermin') ?>
                        <?= Assets::img('icons/16/blue/add.png', array('style' => 'margin-right:20px;',
                                                                       'title' => _('Blocktermin hinzufügen')
                        )) ?>
                    </a>
                </span>
            </nav>
        </header>
        <? $termine = $course->getSingleDates(true, true, true) ?>
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
            <?= MessageBox::info(sprintf(_('Keine unregelmäßigen Termine für %s vorhanden'), $course->name)) ?>
        <? endif; ?>
    </section>
</form>
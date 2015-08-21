<form class="studip-form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Unregelm��ige Termine / Blocktermine') ?>
            </h1>
            <nav>
                <a class="link-add" href="<?= $controller->link_for('course/timesrooms/editIrregular/0') ?>"
                   data-dialog title="<?= _('Neuen Einzeltermin') ?>">
                    <?= _('Neuen Einzeltermin') ?>
                </a>
                <a class="link-add" href="<?= $controller->link_for('course/timesrooms/editBlock/0') ?>" data-dialog
                   title="<? _('Blocktermin hinzuf�gen') ?>">
                    <?= _('Neuen Blocktermin') ?>
                </a>
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
                                <?= _('alle Ausw�hlen') ?>
                            </label>
                        </section>
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
            <?= MessageBox::info(sprintf(_('Keine unregelm��igen Termine f�r %s vorhanden'), $course->name)) ?>
        <? endif; ?>
    </section>
</form>
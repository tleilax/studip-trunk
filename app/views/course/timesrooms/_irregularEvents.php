<section class="contentbox">
    <header>
        <h1>
            <?= _('Unregelmäßige Termine / Blocktermine') ?>
        </h1>
        <nav>
            <a data-dialog-button class="link-add"
               href="<?= $controller->link_for('course/timesrooms/createSingleDate/' . $course_id, $editParams) ?>"
               data-dialog="size=big" title="<?= _('Neuen Einzeltermin') ?>">
                <?= _('Neuen Einzeltermin') ?>
            </a>
            <a data-dialog-button class="link-add"
               href="<?= $controller->url_for('course/block_appointments/index/' . $course_id, $editParams) ?>"
               data-dialog="size=big"
               title="<? _('Blocktermin hinzufügen') ?>">
                <?= _('Neuen Blocktermin') ?>
            </a>
        </nav>
    </header>
    <? if (!empty($single_dates)) : ?>
        <form class="studip-form" action="<?= $controller->url_for('course/timesrooms/stack', $editParams) ?>"
              data-dialog="size=big"  method="post">
            <table class="default nohover">
                <colgroup>
                    <col width="30px">
                    <col width="30%">
                    <col>
                    <col width="20%">
                    <col width="50px">
                </colgroup>

                <? foreach ($single_dates as $semester_id => $termine) : ?>
                    <thead>
                    <tr>
                        <th colspan="5"><?= htmlReady(Semester::find($semester_id)->name) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <? foreach ($termine as $termin) : ?>
                        <?= $this->render_partial('course/timesrooms/_cycleRow.php', array('termin'    => $termin,
                                                                                           'class_ids' => 'ids-irregular'
                        )) ?>
                    <? endforeach ?>
                    </tbody>
                <? endforeach; ?>

                <tfoot>
                <tr>
                    <td colspan="2">
                        <section style="margin: 0; padding: 0">
                            <input data-proxyfor=".ids-irregular" type="checkbox"
                                   id="checkAllIrregular" data-activates=".actionForAllIrregular">
                            <label for="checkAllIrregular" class="horizontal">
                                <?= _('alle Auswählen') ?>
                            </label>
                        </section>
                    </td>
                    <td colspan="4" class="actions">
                        <select name="method" class="actionForAllIrregular">
                            <?= $this->render_partial('course/timesrooms/_stack_actions.php') ?>
                        </select>
                        <?= Studip\Button::create('ausführen', 'run', array('class' => 'actionForAllIrregular')) ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
    <? else : ?>
        <section>
            <p class="text-center">
                <strong>
                    <?= sprintf(_('Keine unregelmäßigen Termine für %s vorhanden'), $course->name) ?>
                </strong>
            </p>
        </section>
    <? endif; ?>
</section>

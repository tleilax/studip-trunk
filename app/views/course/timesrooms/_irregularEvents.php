<section class="contentbox timesrooms">
    <header>
        <h1>
            <?= _('Unregelmäßige Termine / Blocktermine') ?>
        </h1>
        <nav>
            <a class="link-add"
               href="<?= $controller->link_for('course/timesrooms/createSingleDate/' . $course->id, $editParams) ?>"
               data-dialog="size=600" title="<?= _('Einzeltermin hinzufügen') ?>">
                <?= _('Neuer Einzeltermin') ?>
            </a>
            <a class="link-add"
               href="<?= $controller->url_for('course/block_appointments/index/' . $course->id, $editParams) ?>"
               data-dialog="size=600"
               title="<?= _('Blocktermin hinzufügen') ?>">
                <?= _('Neuer Blocktermin') ?>
            </a>
        </nav>
    </header>
    <? if (!empty($single_dates)) : ?>
        <form class="default collapsable" action="<?= $controller->url_for('course/timesrooms/stack', $editParams) ?>"
              <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?>  method="post">
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
                                <?= _('Alle auswählen') ?>
                            </label>
                        </section>
                    </td>
                    <td colspan="4" class="actions">
                        <select name="method" class="actionForAllIrregular">
                            <?= $this->render_partial('course/timesrooms/_stack_actions.php') ?>
                        </select>
                        <?= Studip\Button::create('ausführen', 'run', array('class' => 'actionForAllIrregular', 'data-dialog' => 'size=big')) ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
    <? else : ?>
        <section>
            <p class="text-center">
                <strong>
                    <?= _('Keine unregelmäßigen Termine vorhanden') ?>
                </strong>
            </p>
        </section>
    <? endif; ?>
</section>

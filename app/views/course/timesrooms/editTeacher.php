<h2><strong><?= _('Durchführende Lehrende') ?></strong></h2>
<? if (!empty($related_persons)) : ?>
    <ul class="list-unstyled">
        <? foreach ($related_persons as $dozent) : ?>
            <li>
                <?= htmlReady($dozent['fullname']) ?>
                <?= Assets::img('icons/blue/trash', tooltip2(sprintf(_('%s aus Termin austragen'), htmlReady($dozent['fullname'])))) ?>
            </li>
        <? endforeach; ?>
    </ul>
<? else : ?>
    <p><?= _('Keine Lehrende eingetragen') ?></p>
<? endif ?>

<? if (!empty($dozenten)) : ?>
    <form action="<?= $controller->url_for('course/timesrooms/addRelatedPerson/' . $termin->id) ?>"
          data-dialog="size=50%">
        <section style="margin-top: 20px">
            <label for="add_teacher">
                <?= _('Lehrenden auswählen') ?>
            </label>
            <select id="add_teacher" name="add_teacher">
                <? foreach ($dozenten as $doz_id => $dozent) : ?>
                    <option value="<?= $doz_id ?>">
                        <?= htmlReady($dozent['fullname']) ?>
                    </option>
                <? endforeach; ?>
            </select>
            <?= Assets::input('icons/16/blue/add.png', array('title' => _('Dozenten zu diesem Termin hinzufügen'))) ?>
        </section>
    </form>
<? endif ?>


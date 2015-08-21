<p><strong><?= _('Durchführende Lehrende') ?></strong></p>
<? if (!empty($related_persons)) : ?>
    <ul>
        <? foreach ($related_persons as $dozent) : ?>
            <li>
                <?= htmlReady($dozent['fullname']) ?>
                <?= Assets::img('icons/16/blue/trash.png', tooltip2(sprintf(_('%s aus Termin austragen'), htmlReady($dozent['fullname'])))) ?>
            </li>
        <? endforeach; ?>
    </ul>
<? else : ?>
    <p><?= _('Keine Lehrende eingetragen') ?></p>
<? endif ?>
<? if (!empty($dozenten)) : ?>
    <form action=""
          data-dialog="size=50%">
        <select name="add_teacher" aria-labelledby="<?= _('Lehrenden auswählen') ?>">
            <option><?= _('Lehrenden auswählen') ?></option>
            <? foreach ($dozenten as $doz_id => $dozent) : ?>
                <option value="<?= $doz_id ?>">
                    <?= htmlReady($dozent['fullname']) ?>
                </option>
            <? endforeach; ?>
        </select>
        <?= Assets::input('icons/16/blue/add.png',
            tooltip2(_('Dozenten zu diesem Termin hinzufügen')) +
            array('formaction'  => $controller->url_for('course/timesrooms/addRelatedPerson/' . $termin->id),
                  'data-dialog' => 'size=50%'
            )) ?>
    </form>
<? endif ?>
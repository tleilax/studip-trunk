<table class="default">
    <? if (!Request::isXhr()) : ?>
        <caption><?= _('Durchführende Lehrende') ?></caption><br>
    <? endif ?>
    <thead>
    <tr>
        <th><?= _('Lehrender') ?></th>
        <th><?= _('Aktion') ?></th>
    </tr>
    </thead>
    <tbody>
    <? if (!empty($related_persons)) : ?>
        <? foreach ($related_persons as $dozent) : ?>
            <tr>
                <td>
                    <?= htmlReady($dozent['fullname']) ?>
                </td>
                <td>

                    <?= Assets::img('icons/16/blue/trash.png', tooltip2(sprintf(_('%s aus Termin austragen'), htmlReady($dozent['fullname'])))) ?>
                </td>
            </tr>
        <? endforeach; ?>
    <? else : ?>
        <tr>
            <td colspan="2" style="text-align: center">
                <?= _('Keine Lehrende eingetragen') ?>
            </td>
        </tr>
    <? endif ?>
    </tbody>
    <? if (!empty($dozenten)) : ?>
        <tfoot>
        <tr>
            <td colspan="2">
                <form action="<?= $controller->url_for('course/timesrooms/addRelatedPerson/' . $termin->id) ?>"
                      data-dialog="size=50%">
                    <select name="add_teacher" aria-labelledby="<?= _('Lehrenden auswählen') ?>">
                        <option><?= _('Lehrenden auswählen') ?></option>
                        <? foreach ($dozenten as $doz_id => $dozent) : ?>
                            <option value="<?= $doz_id ?>">
                                <?= htmlReady($dozent['fullname']) ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                    <?= Assets::input('icons/16/blue/add.png', array('title' => _('Dozenten zu diesem Termin hinzufügen'))) ?>
                </form>
            </td>
        </tr>
        </tfoot>
    <? endif ?>
</table>

<div data-dialog-button>
    <?= \Studip\LinkButton::createAccept(_('Zurück zur Terminübersicht'), $controller->url_for('course/timesrooms/index#'. $termin->metadate_id,
        array('contentbox_open' => $termin->metadate_id)), array('data-dialog' => 'size=50%')) ?>
</div>

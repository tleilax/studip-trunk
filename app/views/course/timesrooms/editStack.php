<form method="post" action="<?= $controller->url_for('course/timesrooms/saveStack/' . $cycle_id, $linkAttributes) ?>" class="default"
      data-dialog="size=big">
    <?= CSRFProtection::tokenTag()?>
    <input type="hidden" name="method" value="edit">

    <label>
        <?= _('Durchf�hrende Lehrende') ?>
        <select name="related_persons_action" id="related_persons_action">
            <option value="">-- <?= _('Aktion ausw�hlen') ?> --</option>
            <option value="add">...<?= _('hinzuf�gen') ?></option>
            <option value="delete">...<?= _('entfernen') ?></option>
        </select>
    </label>

    <select name="related_persons[]" id="related_persons" multiple>
        <? foreach ($teachers as $teacher) : ?>
            <option value="<?= htmlReady($teacher['user_id']) ?>"><?= htmlReady($teacher['fullname']) ?></option>
        <? endforeach ?>
    </select>

    <? if (count($gruppen)) : ?>
        <label>
            <?= _('Betrifft die Gruppen') ?>
            <select name="related_groups_action" id="related_groups_action">
                <option value="">-- <?= _('Aktion ausw�hlen') ?> --</option>
                <option value="add">...<?= _('hinzuf�gen') ?></option>
                <option value="delete">...<?= _('entfernen') ?></option>
            </select>
        </label>

        <select id="related_groups" name="related_groups[]" multiple>
            <? foreach ($gruppen as $gruppe) : ?>
                <option value="<?= htmlReady($gruppe->statusgruppe_id) ?>"><?= htmlReady($gruppe->name) ?></option>
            <? endforeach ?>
        </select>
    <? endif ?>


    <p><strong><?= _('Raumangaben') ?></strong></p>
    <? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>
        <? $resList->reset() ?>
        <section>
        <input type="radio" name="action" value="room" checked="checked">
        <label style="display: inline;">
            <select name="room" style="display: inline; " onFocus="jQuery('input[type=radio][name=action][value=room]').prop('checked', 'checked')">
                <option value="0">-- <?= _('Raum ausw�hlen') ?> --</option>
                <? while ($res = $resList->next()) : ?>
                    <option value="<?= $res['resource_id'] ?>">
                        <?= my_substr(htmlReady($res["name"]), 0, 30) ?> <?= $seats[$res['resource_id']] ? '(' . $seats[$res['resource_id']] . ' ' . _('Sitzpl�tze') . ')' : '' ?>
                    </option>
                <? endwhile; ?>
            </select>
            <?= Icon::create('room-clear', 'inactive', ['title' => _("Nur buchbare R�ume anzeigen")])->asImg(16, ["class" => 'bookable_rooms_action', "data-name" => 'bulk_action']) ?>
        </label>
        </section>

        <? $placerholder = _('Freie Ortsangabe (keine Raumbuchung):') ?>
    <? else : ?>
        <? $placerholder = _('Freie Ortsangabe:') ?>
    <? endif ?>
    <section>
    <input type="radio" name="action" value="freetext">
    <label style="display: inline;">
        <input type="text" name="freeRoomText" style="display: inline;" value="<?= $tpl['freeRoomText'] ?>"
               placeholder="<?= $placerholder ?>"
               onFocus="jQuery('input[type=radio][name=action][value=freetext]').prop('checked', 'checked')">
    </label>
    </section>
    <? if (Config::get()->RESOURCES_ENABLE) : ?>
        <label>
            <input type="radio" name="action" value="noroom" style="display:inline">
            <?= _('Kein Raum') ?>
        </label>
    <? endif ?>

    <label>
        <input type="radio" name="action" value="nochange" checked="checked">
        <?= _('Keine �nderungen an den Raumangaben vornehmen') ?>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('�nderungen speichern'), 'save') ?>
        <? if (Request::int('fromDialog')) : ?>
            <?= Studip\LinkButton::create(_('Zur�ck zur �bersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big')) ?>
        <? endif ?>
    </footer>
</form>

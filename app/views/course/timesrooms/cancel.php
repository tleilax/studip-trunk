<section>
    <p>
        <strong> <?= _('Wenn Sie die nicht stattfindenden Termine mit einem Kommentar versehen, werden die Ausfalltermine im Ablaufplan weiterhin dargestellt und auch im Terminkalender eingeblendet.') ?></strong>
    </p>

    <label for="cancel_comment">
        <?= _('Kommentar') ?>
    </label>
    <textarea rows="5" class="size-xl" name="cancel_comment"
              id="cancel_comment"><?= htmlReady($termin->getComment()) ?></textarea>
    <input type="checkbox" id="cancel_send_message" name="cancel_send_message" value="1"/>
    <label for="cancel_send_message" class="horizontal">
        <?= _('Benachrichtigung über ausfallende Termine an alle Teilnehmer verschicken') ?>
    </label>

</section>
<footer>
    <?= Studip\Button::createAccept(_('Übernehmen'), 'editDeletedSingleDate',
        array('formaction'  => $controller->url_for('course/timesrooms/saveComment/' . $termin->getTerminID()),
              'data-dialog' => 'size=big'
        )) ?>
    <?= Studip\LinkButton::createCancel(_('Abbrechen'), '?#' . $termin_id) ?>
</footer>

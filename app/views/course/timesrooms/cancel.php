<?= $this->render_partial('course/timesrooms/_cancel_form.php', compact('termin'))?>
<footer>
    <?= Studip\Button::createAccept(_('�bernehmen'), 'editDeletedSingleDate', array(
            'formaction'  => $controller->url_for('course/timesrooms/saveComment/' . $termin->id)
    ) + $params) ?>
    <?= Studip\LinkButton::createCancel(_('Abbrechen'), '?#' . $termin_id) ?>
</footer>

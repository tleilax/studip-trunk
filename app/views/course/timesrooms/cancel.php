<?= $this->render_partial('course/timesrooms/_cancel_form.php', compact('termin'))?>
<footer>
    <?= Studip\Button::createAccept(_('�bernehmen'), 'editDeletedSingleDate',
        array('formaction'  => $controller->url_for('course/timesrooms/saveComment/' . $termin->getTerminID()),
              'data-dialog' => 'size=big;reload-on-close'
        )) ?>
    <?= Studip\LinkButton::createCancel(_('Abbrechen'), '?#' . $termin_id) ?>
</footer>

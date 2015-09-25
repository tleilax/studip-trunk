<section>
    <p>
        <strong> <?= _('Wenn Sie die nicht stattfindenden Termine mit einem Kommentar versehen, werden die Ausfalltermine im Ablaufplan weiterhin dargestellt und auch im Terminkalender eingeblendet.') ?></strong>
    </p>

    <label for="cancel_comment">
        <?= _('Kommentar') ?>
    </label>
    <textarea rows="5" class="size-xl" name="cancel_comment"
              id="cancel_comment"><?= $termin instanceof CourseExDate ? (isset($termin->content) ? $termin->content : '') : '' ?></textarea>
    <input type="checkbox" id="cancel_send_message" name="cancel_send_message" value="1"/>
    <label for="cancel_send_message" class="horizontal">
        <?= _('Benachrichtigung über ausfallende Termine an alle Teilnehmer verschicken') ?>
    </label>

</section>
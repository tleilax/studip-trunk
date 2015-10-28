<section>
    <p>
        <strong> <?= _('Wenn Sie die nicht stattfindenden Termine mit einem Kommentar versehen, werden die Ausfalltermine im Ablaufplan weiterhin dargestellt und auch im Terminkalender eingeblendet.') ?></strong>
    </p>

    <label for="cancel_comment">
        <?= _('Kommentar') ?>
    </label>

    <? $content = '' ?>
    <? if ($termin instanceof CourseExDate && isset($termin->content)) : ?>
        <? $content = $termin->content ?>
    <? endif; ?>
    <textarea rows="5" name="cancel_comment"
              id="cancel_comment"><?= $content?>
    </textarea>
    <input type="checkbox" id="cancel_send_message" name="cancel_send_message" value="1"/>
    <label for="cancel_send_message" class="horizontal">
        <?= _('Benachrichtigung über ausfallende Termine an alle Teilnehmer verschicken') ?>
    </label>

</section>
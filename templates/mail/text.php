<?
# Lifter010: TODO
?>
<?= $message ?>

<? if (isset($attachments) && count($attachments)) : ?>

    <?= _("Dateianhänge:") ?>

    <? foreach ($attachments as $attachment) : ?>
        <?= $attachment->name . ' (' . relsize($attachment->file->size, false) . ')' ?>

        <?= $attachment->getDownloadURL() ?>


    <? endforeach; ?>
<? endif; ?>


--
<?= sprintf(_("Diese E-Mail ist eine Kopie einer systeminternen Nachricht, die in Stud.IP an %s versendet wurde."), $rec_fullname) ?>

<?= sprintf(_("Sie erreichen Stud.IP unter %s"), $GLOBALS['ABSOLUTE_URI_STUDIP']) ?>

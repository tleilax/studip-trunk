<?
# Lifter010: TODO
?>
<h1><?= _("Einrichtungsbild hochladen") ?></h1>

<?= MessageBox::success(_("Die Bilddatei wurde erfolgreich hochgeladen.")) ?>

<p class="quiet">
    <?= _("Eventuell sehen Sie das neue Bild erst, nachdem Sie diese Seite neu geladen haben (in den meisten Browsern F5 dr�cken).") ?>
</p>

<p>
    <a href="<?= URLHelper::getLink('admin_institut.php?i_id=' . $institute_id) ?>">
      <?= Assets::img('icons/16/blue/arr_1left.png', array('style' => 'vertical-align: baseline;')) ?>
      <?= _("zur�ck zur Einrichtungsadministration") ?>
    </a>
</p>

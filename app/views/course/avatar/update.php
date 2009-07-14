<?
$GLOBALS['CURRENT_PAGE'] = getHeaderLine($course_id) . ' - ' .
                           _('Bild �ndern');
$tabs = "links_admin";

$this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'))

?>

<style>
label {
    display: block;
    font-weight: bold;
}
</style>

<? if (isset($error)) : ?>
    <?= MessageBox::error($error) ?>
<? endif ?>

<h1><?= _("Veranstaltungsbild hochladen") ?></h1>

<div style="float: left; padding: 0 1em 1em 0;">
    <?= CourseAvatar::getAvatar($course_id)->getImageTag(Avatar::NORMAL) ?>
</div>

<form enctype="multipart/form-data"
      action="<?= $controller->url_for('course/avatar/put?cid=' . $course_id) ?>"
      method="post" style="float: left">
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
    <label for="upload-input"><?= _("W�hlen Sie ein Bild f�r die Veranstaltung:") ?></label>
    <input id="upload-input" name="avatar" type="file">

    <p class="quiet">
        <?= Assets::img("info.gif", array('style' => 'vertical-align: middle;')) ?>
        <? printf(_("Die Bilddatei darf max. %d KB gro� sein, es sind nur Dateien mit den Endungen %s, %s oder %s erlaubt!"),
                  Avatar::MAX_FILE_SIZE / 1024,
                  '<b>.jpg</b>', '<b>.png</b>', '<b>.gif</b>') ?>
    </p>

    <p>
        <?= makeButton('absenden', 'input') ?>
        <span class="quiet">
            <?= _("oder") ?> <a href="<?= URLHelper::getLink('admin_seminare1.php') ?>"><?= _("abbrechen") ?></a>
        </span>
    </p>
</form>


<? if (isset($error)) : ?>
    <?= MessageBox::error($error) ?>
<? endif ?>

<h1><?= _("Veranstaltungsbild hochladen") ?></h1>

<div style="float: left; padding: 0 1em 1em 0;">
    <? if ($this->studygroup_mode) : ?>
    <?= StudygroupAvatar::getAvatar($course_id)->getImageTag(Avatar::NORMAL) ?>
    <? else: ?>
    <?= CourseAvatar::getAvatar($course_id)->getImageTag(Avatar::NORMAL) ?>
    <? endif ?>
</div>

<form enctype="multipart/form-data"
      action="<?= $controller->url_for('course/avatar/put/' . $course_id) ?>"
      method="post" style="float: left">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
    <label for="upload-input"><?= _("W�hlen Sie ein Bild f�r die Veranstaltung:") ?></label>
    <input id="upload-input" name="avatar" type="file">

    <p class="quiet">
        <?= Assets::img("icons/16/grey/info-circle.png", array('style' => 'vertical-align: middle;')) ?>
        <? printf(_("Die Bilddatei darf max. %d KB gro� sein, es sind nur Dateien mit den Endungen %s, %s oder %s erlaubt!"),
                  Avatar::MAX_FILE_SIZE / 1024,
                  '<b>.jpg</b>', '<b>.png</b>', '<b>.gif</b>') ?>
    </p>

    <p>
        <?= makeButton('absenden', 'input') ?>
        <span class="quiet">
            <?= _("oder") ?>
            <? if ($this->studygroup_mode) : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/course/studygroup/edit/' . $course_id) ?>">
            <? else : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/course/basicdata/view/' . $course_id) ?>">
            <? endif ?>
            <?= makeButton('abbrechen') ?>
            </a>
        </span>
    </p>
</form>


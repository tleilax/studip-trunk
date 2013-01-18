<?php
use Studip\Button, Studip\LinkButton;

//Infobox:
$info = array();
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Hier können Sie die Regeln, Eigenschaften und ".
                        "Zuordnungen des Anmeldesets bearbeiten.");
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Sie können das Anmeldeset allen Einrichtungen zuordnen, ".
                        "an denen Sie mindestens Dozentenrechte haben.");

$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Alle Veranstaltungen der Einrichtungen, an denen Sie ".
                        "mindestens Dozentenrechte haben, können zum ".
                        "Anmeldeset hinzugefügt werden.");

$infobox = array(
    array("kategorie" => _('Informationen:'),
          "eintrag" => $info
    )
);
$infobox = array('content' => $infobox,
                 'picture' => 'infobox/administration.png'
);

?>
<h2><?= $courseset ? _('Anmeldeset bearbeiten') : _('Anmeldeset anlegen') ?></h2>
<form action="<?= $controller->url_for('admission/courseset/save', ($courseset ? $courseset->getId() : '')) ?>" method="post">
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"><?= _('Name des Anmeldesets:') ?></div>
        <div style="display: inline-block; vertical-align: top;">
            <input type="text" size="60" maxlength="255" name="name" value="<?= $courseset ? $courseset->getName() : '' ?>" required/>
        </div>
    </div>
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"><?= _('Einrichtungszuordnung:') ?></div>
        <div style="display: inline-block; vertical-align: top;">
            <?php foreach ($this->myInstitutes as $institute) { ?>
            <input type="checkbox" name="institutes[]" value="<?= $institute['Institut_id'] ?>"/> <?= $institute['Name'] ?><br/>
            <?php } ?>
        </div>
    </div>
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"><?= _('Anmelderegeln:') ?></div>
        <div style="display: inline-block; vertical-align: top;" id="rules">
            <?php if ($courseset) { ?>
            <div id="rulelist">
                <?php foreach ($courseset->getAdmissionRules() as $rule) { ?>
                <div class="rule" id="rule_<?= $rule->getId() ?>">
                    <input type="checkbox" name="rules[]" value="<?= $rule->getId() ?>"/><?= $rule->getName() ?><br/>
                </div>
                <?php } ?>
            </div>
            <?php } else { ?>
                <span id="norules">
                    <i><?= _('Sie haben noch keine Anmelderegeln festgelegt.') ?></i>
                </span>
                <br/>
            <?php } ?>
            <br/>
            <a href="<?= $controller->url_for('admission/rule/configure') ?>" onclick="return STUDIP.Admission.configureRule(null, '<?= $controller->url_for('admission/rule/configure') ?>');">
                <?= Assets::img('icons/16/red/plus.png', array(
                    'alt' => _('Anmelderegel hinzufügen'),
                    'title' => _('Anmelderegel hinzufügen'))) ?><?= _('Anmelderegel hinzufügen') ?></a>
        </div>
    </div>
    <div style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <div align="right" style="display: inline-block; vertical-align: top; width: 20%; font-weight: bold;"><?= _('Veranstaltungszuordnung:') ?></div>
        <div style="display: inline-block; vertical-align: top;" id="inst_courses">
            <?php
            if ($courseset) {
                $courseIds = $courseset->getCourses();
            }
            foreach ($this->courses as $course) {
                $title = $course['Name'];
                if ($course['VeranstaltungsNummer']) {
                    $title = $course['VeranstaltungsNummer'].' | '.$title;
                } 
            ?>
            <input type="checkbox" name="courses[]" value="<?= $course['seminar_id'] ?>"/> <?= $title ?><br/>
            <?php } ?>
        </div>
    </div>
    <div align="center" style="width: 80%; padding: 10px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <?= Button::create(_('Speichern'), 'submit') ?>
        <?= Button::create(_('Abbrechen'), 'cancel') ?>
    </div>
</form>
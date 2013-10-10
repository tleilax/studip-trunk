<?php
use Studip\Button, Studip\LinkButton;

//Infobox:
$info = array();
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Hier k�nnen Sie die Regeln, Eigenschaften und ".
                        "Zuordnungen des Anmeldesets bearbeiten.");
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Sie k�nnen das Anmeldeset allen Einrichtungen zuordnen, ".
                        "an denen Sie mindestens Dozentenrechte haben.");

$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Alle Veranstaltungen der Einrichtungen, an denen Sie ".
                        "mindestens Dozentenrechte haben, k�nnen zum ".
                        "Anmeldeset hinzugef�gt werden.");

$infobox = array(
    array("kategorie" => _('Informationen:'),
          "eintrag" => $info
    )
);
$infobox = array('content' => $infobox,
                 'picture' => 'infobox/administration.png'
);

// Load assigned course IDs.
$courseIds = $courseset ? $courseset->getCourses() : array();
// Load assigned user list IDs.
$userlistIds = $courseset ? $courseset->getUserlists() : array();
?>
<?= $this->render_partial('dialog/confirm_dialog') ?>
<h1><?= $courseset ? _('Anmeldeset bearbeiten') : _('Anmeldeset anlegen') ?></h1>
<form class="studip_form" action="<?= $controller->url_for('admission/courseset/save', ($courseset ? $courseset->getId() : '')) ?>" method="post">
    <label for="name" class="caption">
        <?= _('Name des Anmeldesets:') ?>
        <span class="required">*</span>
    </label>
    <input type="text" size="60" maxlength="255" name="name"
        value="<?= $courseset ? htmlReady($courseset->getName()) : '' ?>"
        required="required" aria-required="true"
        placeholder="<?= _('Bitte geben Sie einen Namen f�r das Anmeldeset an') ?>"/>
    <label for="institute_id" class="caption">
        <?= _('Einrichtungszuordnung:') ?>
        <span class="required">*</span>
    </label>
    <div id="institutes">
    <?php if ($myInstitutes) { ?>
        <?php if ($instSearch) { ?>
            <?= $instTpl ?>
        <?php } else { ?>
            <?php foreach ($myInstitutes as $institute) { ?>
            <input type="checkbox" name="institutes[]" value="<?= $institute['Institut_id'] ?>"
                <?= $selectedInstitutes[$institute['Institut_id']] ? 'checked="checked"' : '' ?>
                class="institute" onclick="STUDIP.Admission.getCourses('institute', 'instcourses', 
                '<?= $controller->url_for('admission/courseset/instcourses', $courseset ? $courseset->getId() : '') ?>', 'courselist')"/>
                <?= $institute['Name'] ?>
            <br/>
            <?php } ?>
        <?php } ?>
    <?php } else { ?>
        <?php if ($instSearch) { ?>
        <div id="institutes">
            <input type="image" src="<?= Assets::image_path('icons/16/yellow/arr_2down') ?>"
                   <?= tooltip(_('Einrichtung hinzuf�gen')) ?> border="0" name="add_institute">
            <?= $instSearch ?>
            <br/><br/>
        </div>
        <i><?=  _('Sie haben noch keine Einrichtung ausgew�hlt. Benutzen Sie obige Suche, um dies zu tun.') ?></i>
        <?php } else { ?>
        <i><?=  _('Sie sind keiner Einrichtung zugeordnet.') ?></i>
        <?php } ?>
    <?php } ?>
    </div>
    <label class="caption">
        <?= _('Veranstaltungszuordnung:') ?>
    </label>
    <?= $coursesTpl; ?>
    <label class="caption" for="add_rule">
        <?= _('Anmelderegeln:') ?>
        <span class="required">*</span>
    </label>
    <div id="rules">
        <?php if ($courseset) { ?>
        <div id="rulelist">
            <?php foreach ($courseset->getAdmissionRules() as $rule) { ?>
                <?= $this->render_partial('admission/rule/save', array('rule' => $rule)) ?>
            <?php } ?>
        </div>
        <?php } else { ?>
        <span id="norules">
            <i><?= _('Sie haben noch keine Anmelderegeln festgelegt.') ?></i>
        </span>
        <br/>
        <?php } ?>
        <div style="clear: both;">
                <?= LinkButton::create(_('Anmelderegel hinzuf�gen'), 
                    $controller->url_for('admission/rule/configure'), 
                    array(
                        'onclick' => "return STUDIP.Admission.configureRule(null, '".$controller->url_for('admission/rule/configure')."')"
                        )
                    ); ?>
        </div>
    </div>
    <label class="caption">
        <?= _('Nutzerlisten zuordnen:') ?>
    </label>
    <?php if ($myUserlists) { ?>
        <?php
        foreach ($myUserlists as $list) {
            $checked = '';
            if (in_array($list->getId(), $userlistIds)) {
                $checked = ' checked="checked"';
            }
        ?>
        <input type="checkbox" name="userlists[]" value="<?= $list->getId() ?>"<?= $checked ?>/> <?= $list->getName() ?><br/>
        <?php } ?>
    <?php } else { ?>
        <i><?=  _('Sie haben noch keine Nutzerlisten angelegt.') ?></i>
    <?php
    }
    // Keep lists that were assigned by other users.
    foreach ($userlistIds as $list) {
        if (!in_array($list, array_keys($myUserlists))) {
    ?>
    <input type="hidden" name="userlists[]" value="<?= $list ?>"/>
    <?php
        }
    }
    ?>
    <label for="infotext" class="caption">
        <?= _('weitere Hinweise:') ?>
    </label>
    <textarea cols="60" rows="3" name="infotext"><?= $courseset ? htmlReady($courseset->getInfoText()) : '' ?></textarea>
    <div class="submit_wrapper">
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::createAccept(_('Speichern'), 'submit') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admission/courseset')) ?>
    </div>
</form>
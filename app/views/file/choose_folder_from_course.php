<?
$options = array();
if (Request::get("to_plugin")) {
    $options['to_plugin'] = Request::get("to_plugin");
}
if (Request::get("range_type")) {
    $options['range_type'] = Request::get("range_type");
}
if (Request::get("fileref_id")) {
    $options['fileref_id'] = Request::get("fileref_id");
}
if (Request::get("isfolder")) {
    $options['isfolder'] = Request::get("isfolder");
}
if (Request::get("copymode")) {
    $options['copymode'] = Request::get("copymode");
}
?>

<script type="text/javascript">
jQuery(function () {
    jQuery("#folderchooser_course_search select option").on("click", function (event) {
    	jQuery('#folderchooser_course_search').submit();
    });
});
</script>

<? if ($GLOBALS['perm']->have_perm("admin")) : ?>
    <form id="folderchooser_course_search"
          action="<?= $controller->link_for("file/choose_folder_from_course/", $options) ?>"
          method="get"
          data-dialog>
        <?= QuickSearch::get("course_id", new StandardSearch("Seminar_id"))
            ->fireJSFunctionOnSelect("function () { jQuery('#folderchooser_course_search').submit(); }")
            ->setInputStyle("width: calc(100% - 40px); margin: 20px;")
            ->render() ?>
    </form>
<? else : ?>
    <table class="default">
        <thead>
            <tr>
                <th><?= _("Bild") ?></th>
                <th><?= _("Name") ?></th>
                <th><?= _("Semester") ?></th>
                <th><?= _("Zum Dateibereich") ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($courses as $course) : ?>
                <tr>
                    <td>
                        <a href="<?= $controller->link_for("file/choose_folder_from_course/", array_merge($options, array('course_id' => $course->getId()))) ?>" data-dialog>
                            <?= CourseAvatar::getAvatar($course->getId())->getImageTag(Avatar::MEDIUM, array('style' => "width: 50px; height: 50px;")) ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= $controller->link_for("file/choose_folder_from_course/", array_merge($options, array('course_id' => $course->getId()))) ?>" data-dialog>
                            <?= htmlReady($course->name) ?>
                        </a>
                    </td>
                    <td>
                        <?= htmlReady($course->start_semester->name) ?>
                    </td>
                    <td>
                        <a href="<?= $controller->link_for("file/choose_folder_from_course/", array_merge($options, array('course_id' => $course->getId()))) ?>" data-dialog>
                            <?= Icon::create("folder-full", "clickable")->asImg(30) ?>
                        </a>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? endif ?>

<div data-dialog-button>
    <?= Studip\LinkButton::create(_("Zurück"), $controller->url_for('file/choose_destination/', $options), array('data-dialog' => 1)) ?>
</div>

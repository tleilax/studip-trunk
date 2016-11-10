<?
$options = array();
if (Request::get("to_plugin")) {
    $options['to_plugin'] = Request::get("to_plugin");
}
if (Request::get("range_type")) {
    $options['range_type'] = Request::get("range_type");
}
?>

<? if ($GLOBALS['perm']->have_perm("admin")) : ?>
    <form id="filechooser_course_search"
          action="<?= $controller->link_for("files/choose_file_from_course/".$folder_id) ?>"
          method="get"
          data-dialog>
        <?= QuickSearch::get("course_id", new StandardSearch("Seminar_id"))
            ->fireJSFunctionOnSelect("function () { jQuery('#filechooser_course_search').submit(); }")
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
                        <a href="<?= $controller->link_for("files/choose_file_from_course/".$folder_id, array_merge($options, array('course_id' => $course->getId()))) ?>" data-dialog>
                            <?= CourseAvatar::getAvatar($course->getId())->getImageTag(Avatar::MEDIUM, array('style' => "width: 50px; height: 50px;")) ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= $controller->link_for("files/choose_file_from_course/".$folder_id, array_merge($options, array('course_id' => $course->getId()))) ?>" data-dialog>
                            <?= htmlReady($course->name) ?>
                        </a>
                    </td>
                    <td>
                        <?= htmlReady($course->start_semester->name) ?>
                    </td>
                    <td>
                        <a href="<?= $controller->link_for("files/choose_file_from_course/".$folder_id, array_merge($options, array('course_id' => $course->getId()))) ?>" data-dialog>
                            <?= Icon::create("folder-full", "clickable")->asImg(30) ?>
                        </a>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? endif ?>

<div data-dialog-button>
    <?= Studip\LinkButton::create(_("zurück"), $controller->url_for('/add_files_window/' . Request::get("to_folder_id"), $options), array('data-dialog' => 1)) ?>
</div>
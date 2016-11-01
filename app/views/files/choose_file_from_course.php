<?
$options = array();
if (Request::get("to_plugin")) {
    $options['to_plugin'] = Request::get("to_plugin");
}
?>

<? if ($GLOBALS['perm']->have_perm("admin")) : ?>
    <form action="<?= $controller->link_for("files/choose_file_from_course/".$folder_id) ?>" method="get">
        <?= QuickSearch::get("course_id", new StandardSearch("Seminar_id"))->render() ?>
    </form>
<? else : ?>
    <table class="default">
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
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? endif ?>

<div data-dialog-button>
    <?= Studip\LinkButton::create(_("zurück"), $controller->url_for('/add_files_window/' . Request::get("to_folder_id"), $options), array('data-dialog' => 1)) ?>
</div>
<? if ($GLOBALS['perm']->have_perm("admin")) : ?>
    <form action="<?= $controller->link_for("files/choose_file_from_course/".$folder_id) ?>" method="get">
        <?= QuickSearch::get("course_id", StandardSearch("Seminar_id"))->render() ?>
    </form>
<? else : ?>

<? endif ?>

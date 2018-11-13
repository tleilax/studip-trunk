<html>
    <head>
       <meta charset="UTF-8">
        <title><?= _('Export Veranstaltungsübersicht') ?></title>
        <style>

        </style>
    </head>
    <body>
    <? if (!empty($sem_courses)) : ?>
        <div id="my_seminars">
            <? foreach ($sem_courses as $sem_key => $course_group) : ?>
                <h2><?= htmlReady($sem_data[$sem_key]['name']) ?></h2>
                <? $course_collection = $course_group ?>
                <?= $this->render_partial("my_courses/_exportcourse", compact('course_collection')) ?>
                <hr>
            <? endforeach ?>
        </div>
    <? endif ?>
    </body>
</html>

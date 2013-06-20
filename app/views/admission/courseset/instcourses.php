<div id="tree">
    <ul>
    <?php
    foreach ($allCourses as $semesterId => $semester) {
    ?>
        <li id="<?= $semesterId ?>" rel="semester">
            <a href=""><?= $semester['name'] ?></a>
            <ul>
        <?php foreach ($semester['courses'] as $course) {
            $title = $via_ajax ? utf8_encode($course['Name']) : $course['Name'];
            if ($course['VeranstaltungsNummer']) {
                $title = $course['VeranstaltungsNummer'].' | '.$title;
            }
            if (in_array($course['seminar_id'], $selectedCourses)) {
                $selected = ' checked="checked"';
            } else {
                $selected = '';
            }
            ?>
                <li id="<?= $course['seminar_id'] ?>" rel="course">
                    <input type="checkbox" name="courses[]" value="<?= $course['seminar_id'] ?>"<?= $selected ?>/> <a href=""><?= $title ?></a>
                </li>
        <?php } ?>
            </ul>
        </li>
    <?php } ?>
    </ul>
    <script>
        $(function() {
            $('#tree').bind('loaded.jstree', function (event, data) {
                // Show checked checkboxes.
                var checkedItems = $('#tree').find('.jstree-checked');
                checkedItems.removeClass('jstree-unchecked');
                // Open parent nodes of checked nodes.
                checkedItems.parents().each(function () { data.inst.open_node(this, false, true); });
                // Hide checkbox on all non-courses.
                $(this).find('li[rel!=course]').find('.jstree-checkbox:first').hide();
            }).jstree({
                'core': {
                    'animation': 100,
                    'open_parents': true
                },
                'checkbox': {
                    'real_checkboxes': true,
                    'selected_parent_open': true,
                    'override_ui': false,
                    'two_state': true
                },
                'types': {
                    'types': {
                        'default': {
                            'select_node': function(event) {
                                this.toggle_node(event);
                                return false;
                            }
                        },
                        'semester': {
                            'icon': {
                                'image': '<?= Assets::image_path('icons/16/blue/group.png')?>'
                            }
                        },
                        'course': {
                            'icon': {
                                'image': '<?= Assets::image_path('icons/16/blue/seminar.png')?>'
                            }
                        }
                    }
                },
                'plugins': [ 'html_data', 'themes', 'types', 'checkbox', 'ui' ]
            });
        });
    </script>
</div>
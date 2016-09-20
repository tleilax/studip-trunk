<?php
SkipLinks::addIndex(_('Mitarbeiterliste'), 'list_institute_members');    
?>

<? if ($institute): ?>
    <table class="default" id="list_institute_members">
        <caption><?= _('Mitarbeiterinnen und Mitarbeiter') ?></caption>
        <colgroup>
        <? foreach ($table_structure as $key => $field): ?>
            <? if ($key !== 'statusgruppe'): ?>
                <col width="<?= $field['width'] ?>">
            <? endif; ?>
        <? endforeach; ?>
        </colgroup>
        <thead>
            <tr>
            <? foreach ($table_structure as $key => $field): ?>
                <th <? if ($key === 'actions') echo 'class="actions"'; ?>>
                <? if ($field['link']): ?>
                    <a href="<?= URLHelper::getLink($field['link']) ?>">
                        <?= htmlReady($field['name']) ?>
                    </a>
                <? else: ?>
                    <?= htmlReady($field['name']) ?>
                <? endif; ?>
                </th>
            <? endforeach; ?>
            </tr>
        </thead>
        <?= $table_content ?>
    </table>
<? endif; ?>

<?php
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/person-sidebar.png');
$widget = new ViewsWidget();
$widget->addLink(_('Standard'), URLHelper::getURL('?extend=no'))->setActive($extend != 'yes');
$widget->addLink(_('Erweitert'), URLHelper::getURL('?extend=yes'))->setActive($extend == 'yes');
$sidebar->addWidget($widget);

if ($admin_view) {

    if (!LockRules::Check($institute->id, 'participants')) {

        $edit = new SidebarWidget();
        $edit->setTitle(_('Personenverwaltung'));
        $edit->addElement(new WidgetElement($mp));
        $sidebar->addWidget($edit);
    }


    if (!empty($mail_list)) {
        $actions = new ActionsWidget();
        $actions->addLink(_('Stud.IP Rundmail'), $controller->url_for('messages/write', array('inst_id' => $inst_id, 'emailrequest' => 1)), Icon::create('mail', 'clickable'), array('data-dialog' => 'size=50%'));
        $sidebar->addWidget($actions);
    }
}


$widget = new OptionsWidget();
$widget->setTitle(_('Gruppierung'));
// Admins can choose between different grouping functions
if ($GLOBALS['perm']->have_perm("admin")) {
    $widget->addRadioButton(_('Funktion'),
        URLHelper::getLink('?show=funktion'),
        $show == 'funktion');
    $widget->addRadioButton(_('Status'),
        URLHelper::getLink('?show=status'),
        $show == 'status');
    $widget->addRadioButton(_('keine'),
        URLHelper::getLink('?show=liste'),
        $show == 'liste');
} else {
    $widget->addRadioButton(_('Nach Funktion gruppiert'),
        URLHelper::getLink('?show=funktion'),
        $show == 'funktion');
    $widget->addRadioButton(_('Alphabetische Liste'),
        URLHelper::getLink('?show=liste'),
        $show == 'liste');
}
$sidebar->addWidget($widget);
if (get_config('EXPORT_ENABLE') && $GLOBALS['perm']->have_perm('tutor')) {
    $widget = new ExportWidget();
    $widget->addElement(new WidgetElement(export_form_sidebar($institute->id, "person", $GLOBALS['SessSemName'][0])));
    $sidebar->addWidget($widget);
}

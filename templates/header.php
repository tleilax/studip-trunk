<?php
# Lifter010: TODO

$nav_items = Navigation::getItem('/')->getIterator()->getArrayCopy();
$nav_items = array_filter($nav_items, function ($item) {
    return $item->isVisible(true);
});

$header_nav = ['visible' => $nav_items, 'hidden' => []];
if (isset($_COOKIE['navigation-length'])) {
    $header_nav['hidden'] = array_splice(
        $header_nav['visible'],
        $_COOKIE['navigation-length']
    );
}
?>

<!-- Leiste unten -->
<div id="barBottomContainer">
    <div id="barBottomLeft">
        <input type="checkbox" id="barTopMenu-toggle">
        <label for="barTopMenu-toggle">
            <?= _('Menü') ?>
        </label>
        <? // The main menu will be placed here when scrolled, see navigation.less ?>
        <div id="barTopFont">
            <?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>
        </div>
    </div>
    <!-- Dynamische Links ohne Icons -->
    <div id="barBottomright">
        <ul>

        <? if (Navigation::hasItem('/links')): ?>
            <? foreach (Navigation::getItem('/links') as $nav): ?>
                <? if ($nav->isVisible()) : ?>
                    <li <? if ($nav->isActive()) echo 'class="active"'; ?>>
                        <a
                            <? if (is_internal_url($url = $nav->getURL())) : ?>
                                href="<?= URLHelper::getLink($url) ?>"
                            <? else: ?>
                                href="<?= htmlReady($url) ?>" target="_blank"
                            <? endif; ?>
                            <? if ($nav->getDescription()): ?>
                                title="<?= htmlReady($nav->getDescription()) ?>"
                            <? endif; ?>
                            ><?= htmlReady($nav->getTitle()) ?></a>
                    </li>
                <? endif; ?>
            <? endforeach; ?>
        <? endif; ?>

        <? if (isset($search_semester_nr)) : ?>
            <? if (PageLayout::hasCustomQuicksearch()): ?>
                <?= PageLayout::getCustomQuicksearch() ?>
            <? else: ?>
                <li id="quicksearch_item">
                    <form id="quicksearch" role="search" action="<?= URLHelper::getLink('dispatch.php/search/courses', array('send' => 'yes', 'group_by' => '0') + $link_params) ?>" method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <script>
                            var selectSem = function (seminar_id, name) {
                                document.location = "<?= URLHelper::getURL("dispatch.php/course/details/", array("send_from_search" => 1, "send_from_search_page" => URLHelper::getURL("dispatch.php/search/courses?keep_result_set=1")))  ?>&sem_id=" + seminar_id;
                            };
                        </script>
                        <?php
                        print QuickSearch::get("search_sem_quick_search", new SeminarSearch())
                            ->setAttributes(array(
                                "title" => sprintf(_('Nach Veranstaltungen suchen (%s)'), htmlready($search_semester_name)),
                                "class" => "quicksearchbox expand-to-left"
                            ))
                            ->fireJSFunctionOnSelect("selectSem")
                            ->noSelectbox()
                            ->render();
                        //Komisches Zeugs, das die StmBrowse.class.php braucht:
                        print '<input type="hidden" name="search_sem_1508068a50572e5faff81c27f7b3a72f" value="1">';
                        //Ende des komischen Zeugs.
                        ?>
                        <input type="hidden" name="search_sem_sem" value="<?= $search_semester_nr ?>">
                        <input type="hidden" name="search_sem_qs_choose" value="title_lecturer_number">
                        <?= Icon::create('search', 'info_alt')->asInput([
                            'title' => sprintf(_('Nach Veranstaltungen suchen (%s)'), htmlready($search_semester_name)),
                            'type'  => 'image',
                            'class' => 'quicksearchbutton',
                            'name'  => 'search_sem_do_search',
                            'value' => 'OK',
                        ]) ?>
                    </form>
                </li>
            <? endif; ?>
        <? endif; ?>

        <? if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('autor')): ?>

            <? $active = false;
                if (Navigation::hasItem('/profile')) {
                    $active = Navigation::getItem('/profile')->isActive();
                }
            ?>

            <!-- User-Avatar -->
            <li class="header_avatar_container <?= $active ? 'active' : '' ?>" id="barTopAvatar">

            <? if (is_object($GLOBALS['perm']) && PersonalNotifications::isActivated() && $GLOBALS['perm']->have_perm('autor')) : ?>
                <? $notifications = PersonalNotifications::getMyNotifications() ?>
                <? $lastvisit = (int)UserConfig::get($GLOBALS['user']->id)->getValue('NOTIFICATIONS_SEEN_LAST_DATE') ?>
                <div id="notification_container"<?= count($notifications) > 0 ? ' class="hoverable"' : '' ?>>
                    <? foreach ($notifications as $notification) {
                        if ($notification['mkdate'] > $lastvisit) {
                            $alert = true;
                        }
                    } ?>
                    <div id="notification_marker"<?= $alert ? ' class="alert"' : "" ?> title="<?= _("Benachrichtigungen") ?>" data-lastvisit="<?= $lastvisit ?>">
                        <?= count($notifications) ?>
                    </div>
                    <div class="list below" id="notification_list">
                        <ul>
                        <? foreach ($notifications as $notification) : ?>
                            <?= $notification->getLiElement() ?>
                        <? endforeach ?>
                        </ul>
                    </div>
                <? if (PersonalNotifications::isAudioActivated()): ?>
                    <audio id="audio_notification" preload="none">
                        <source src="<?= Assets::url('sounds/blubb.ogg') ?>" type="audio/ogg">
                        <source src="<?= Assets::url('sounds/blubb.mp3') ?>" type="audio/mpeg">
                    </audio>
                <? endif; ?>
                </div>
            <? else: ?>
                <div id="notification_container"></div>
            <? endif; ?>


                <div id="header_avatar_menu">
                <?php
                    $action_menu = ContentGroupMenu::get();
                    $action_menu->setLabel(User::findCurrent()->getFullName());
                    $action_menu->setIcon(Avatar::getAvatar(User::findCurrent()->id)->getImageTag(Avatar::MEDIUM));
                    $action_menu->addLink(
                        URLHelper::getURL('dispatch.php/profile', array(), true),
                        _('Profil'),
                        Icon::create('person', 'clickable')
                    );
                    $action_menu->addLink(
                        URLHelper::getURL('dispatch.php/files'),
                        _('Meine Dateien'),
                        Icon::create('folder-empty', 'clickable')
                    );
                    $action_menu->addLink(
                        URLHelper::getURL('dispatch.php/settings/account'),
                        _('Nutzerdaten'),
                        Icon::create('key', 'clickable')
                    );
                    $action_menu->addLink(
                        URLHelper::getURL('dispatch.php/settings/general'),
                        _('Einstellungen'),
                        Icon::create('admin', 'clickable')
                    );
                    $action_menu->addLink(
                        URLHelper::getURL('logout.php'),
                        _('Logout'),
                        Icon::create('door-leave', 'clickable')
                    );
                ?>
                <?= $action_menu->render(); ?>
               </div>
               <?= Icon::create('arr_1down', 'info_alt', array('id' => 'avatar-arrow')); ?>


            </li>
        <? endif; ?>

        </ul>
    </div>
</div>
<!-- Ende Header -->

<!-- Start Header -->
<div id="flex-header">
    <!--<div id='barTopLogo'>
        <?= Assets::img('logos/logoneu.jpg', array('alt' => 'Logo Uni Göttingen')) ?>
    </div>
     -->

    <? SkipLinks::addIndex(_('Hauptnavigation'), 'barTopMenu', 1); ?>
    <ul id="barTopMenu" role="navigation" <? if (count($header_nav['hidden']) > 0) echo 'class="overflown"'; ?>>
    <? foreach ($header_nav['visible'] as $path => $nav): ?>
        <?= $this->render_partial(
            'header-navigation-item.php',
            compact('path', 'nav', 'accesskey_enabled')
        ) ?>
    <? endforeach; ?>
        <li class="overflow">
            <input type="checkbox" id="header-sink">
            <label for="header-sink">
                <a class="canvasready" href="#">
                    <?= Icon::create('mobile-sidebar', 'navigation')->asImg(28, [
                        'class'  => 'headericon original',
                        'title'  => '',
                        'alt'    => '',
                    ]) ?>
                    <br>
                    <div class="navtitle">
                        <?= _('Weitere') ?>&hellip;
                    </div>
                </a>
            </label>

            <ul>
            <? foreach ($header_nav['hidden'] as $path => $nav) : ?>
                <?= $this->render_partial(
                    'header-navigation-item.php',
                    compact('path', 'nav', 'accesskey_enabled')
                ) ?>
            <? endforeach; ?>
            </ul>
        </li>
    </ul>

    <!-- Stud.IP Logo -->
    <a class="studip-logo" id="barTopStudip" href="http://www.studip.de/" title="Stud.IP Homepage" target="_blank">
        Stud.IP Homepage
    </a>
</div>

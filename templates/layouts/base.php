<?php
# Lifter010: TODO
NotificationCenter::postNotification('PageWillRender', $body_id ? : PageLayout::getBodyElementId());
$navigation = PageLayout::getTabNavigation();
$tab_root_path = PageLayout::getTabNavigationPath();
if ($navigation) {
    $subnavigation = $navigation->activeSubNavigation();
    if ($subnavigation !== null) {
        $nav_links = new NavigationWidget();
        $nav_links->id = 'sidebar-navigation';
        foreach ($subnavigation as $path => $nav) {
            if (!$nav->isVisible()) {
                continue;
            }
            $nav_id = "nav_".implode("_", preg_split("/\//", $tab_root_path, -1, PREG_SPLIT_NO_EMPTY))."_".$path;
            $link = $nav_links->addLink(
                $nav->getTitle(),
                URLHelper::getLink($nav->getURL()),
                null,
                array('id' => $nav_id)
            );
            $link->setActive($nav->isActive());
            if (!$nav->isEnabled()) {
                $link['disabled'] = true;
                $link->addClass('quiet');
            }
        }
        if ($nav_links->hasElements()) {
            Sidebar::get()->insertWidget($nav_links, ':first');
        }
    }
}
?>
<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <title data-original="<?= htmlReady(PageLayout::getTitle()) ?>">
        <?= htmlReady(PageLayout::getTitle() . ' - ' . Config::get()->UNI_NAME_CLEAN) ?>
    </title>
    <script>
        CKEDITOR_BASEPATH = "<?= Assets::url('javascripts/ckeditor/') ?>";
        String.locale = "<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>";
    </script>
    <? if ($_SESSION['_language'] !== 'de_DE'): ?>
        <link rel="localization" hreflang="<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>"
              href="<?= URLHelper::getScriptLink('dispatch.php/localizations/' . $_SESSION['_language']) ?>" type="application/vnd.oftn.l10n+json">
    <? endif ?>

    <script>
    window.STUDIP = {
        ABSOLUTE_URI_STUDIP: "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>",
        ASSETS_URL: "<?= $GLOBALS['ASSETS_URL'] ?>",
        CSRF_TOKEN: {
            name: '<?=CSRFProtection::TOKEN?>',
            value: '<? try {echo CSRFProtection::token();} catch (SessionRequiredException $e){}?>'
        },
        STUDIP_SHORT_NAME: "<?= htmlReady(Config::get()->STUDIP_SHORT_NAME) ?>",
        URLHelper: {
            base_url: "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>",
            parameters: <?= json_encode(URLHelper::getLinkParams(), JSON_FORCE_OBJECT) ?>
        },
        jsupdate_enable: <?= json_encode(
                         is_object($GLOBALS['perm']) &&
                         $GLOBALS['perm']->have_perm('autor') &&
                         PersonalNotifications::isActivated()) ?>,
        wysiwyg_enabled: <?= json_encode((bool) Config::get()->WYSIWYG) ?>
    }
    </script>

    <?= PageLayout::getHeadElements() ?>

    <script>
    window.STUDIP.editor_enabled = <?= json_encode((bool) Studip\Markup::editorEnabled()) ?> && CKEDITOR.env.isCompatible;
    </script>
</head>

<body id="<?= $body_id ? $body_id : PageLayout::getBodyElementId() ?>">
<div id="layout_wrapper">
    <? SkipLinks::insertContainer() ?>
    <? SkipLinks::addIndex(_("Hauptinhalt"), 'layout_content', 100, true) ?>
    <?= PageLayout::getBodyElements() ?>

    <? include 'lib/include/header.php' ?>

    <div id="layout_page">
        <? if (PageLayout::isHeaderEnabled() && is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody' && Navigation::hasItem('/course') && Navigation::getItem('/course')->isActive() && $_SESSION['seminar_change_view_'.Context::getId()]) : ?>
            <?= $this->render_partial('change_view', array('changed_status' => $_SESSION['seminar_change_view_'.Context::getId()])) ?>
        <? endif ?>

        <? if (PageLayout::isHeaderEnabled() /*&& isset($navigation)*/) : ?>
            <?= $this->render_partial('tabs', compact('navigation')) ?>
        <? endif; ?>

        <?
        if (is_object($GLOBALS['user']) && $GLOBALS['user']->id != 'nobody') {
            // only mark course if user is logged in and free access enabled
            if (Config::get()->ENABLE_FREE_ACCESS
                && Navigation::hasItem('/course')
                && Navigation::getItem('/course')->isActive())
            {
                // indicate to the template that this course is publicly visible
                // need to handle institutes separately (always visible)
                if ($GLOBALS['SessSemName']['class'] == 'inst') {
                    $header_template->public_hint = _('öffentliche Einrichtung');
                } else if (Course::findCurrent()->lesezugriff == 0) {
                    $header_template->public_hint = _('öffentliche Veranstaltung');
                }
            }
        }
        ?>
        <div id="page_title_container">
            <div id="current_page_title">
                <?= htmlReady(PageLayout::getTitle()) ?>
                <?= $public_hint ? '(' . htmlReady($public_hint) . ')' : '' ?>
            </div>
            <? if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('autor')) : ?>
            	<?= Helpbar::get()->render() ?>
            <? endif; ?>
         </div>

        <div id="layout_container">
            <?= Sidebar::get()->render() ?>
            <div id="layout_content">
                <?= implode(PageLayout::getMessages()) ?>
                <?= $content_for_layout ?>
            </div>
        </div>
    </div> <? // Closes #layout_page opened in included templates/header.php ?>

    <?= $this->render_partial('footer'); ?>
    <!-- Ende Page -->
    <? /* <div id="layout_push"></div> */ ?>
</div>


    <?= SkipLinks::getHTML() ?>
</body>
</html>
<?php NotificationCenter::postNotification('PageDidRender', $body_id ? : PageLayout::getBodyElementId());

<!-- Dynamische Links mit Icons -->
<div id='header'>
    <!--<div id='barTopLogo'>
        <img src="<?=$GLOBALS['ASSETS_URL']?>images/logos/logoneu.jpg" alt="Logo Uni G�ttingen">
    </div>
     -->
    <div id="barTopFont">
    <?=$GLOBALS['UNI_NAME']?>
    </div>
    <div id="barTopMenu">
        <ul>
        <? $accesskey = 0 ?>
        <? foreach (Navigation::getItem('/') as $nav) : ?>
            <? if ($nav->isVisible(true)) : ?>
                <?
                $accesskey_attr = '';
                $image = $nav->getImage();

                if ($accesskey_enabled) {
                    $accesskey = ++$accesskey % 10;
                    $accesskey_attr = 'accesskey="' . $accesskey . '"';
                    $image['title'] .= "  [ALT] + $accesskey";
                }
                ?>
                <li>
                <div style="font-size:12px; z-index:2; line-height:90%; padding-bottom:3px">
                <a href="<?= URLHelper::getLink($nav->getURL(), $link_params) ?>" <?= $accesskey_attr ?>>
                <img <? foreach ($image as $key => $value) printf('%s="%s" ', $key, htmlReady($value)) ?>>
                <br>
                <?= htmlReady($nav->getTitle()) ?>
                </a></div>
                </li>
            <? endif ?>
        <? endforeach ?>
        </ul>
    </div>
</div>
<!--Statische Text Links -->
<div id="barTopTools">
    <ul>
        <li>
            <a href="http://blog.studip.de" target="_blank">
            <?=_("Stud.IP Blog")?>
            </a>
        </li>
    </ul>
</div>
<!-- Stud.IP Logo -->
<div id="barTopStudip">
    <a href="http://www.studip.de/" title="Studip Homepage">
        <img src="<?=$GLOBALS['ASSETS_URL']?>images/logos/studipmirror.jpg" alt="Stud.IP Homepage">
    </a>
</div>
<div style="position: relative; margin-top: -34px; margin-right: 42px; float: right; z-index: 2;" align="right">
  <img src="<?=$GLOBALS['ASSETS_URL']?>images/studipdot.gif" alt="Stud.IP Homepage">
</div>
<!-- Leiste unten -->
<div id="barBottomContainer">
    <div id="barBottomLeft">
        <?=($current_page != "" ? _("Aktuelle Seite:") : "")?>
    </div>
    <div id="barBottommiddle">
        <?=($current_page != "" ? htmlReady($current_page) : "")?>
    </div>
    <!-- Dynamische Links ohne Icons -->
    <div id="barBottomright">
        <ul>
            <? if (isset($search_semester_nr)) : ?>
            <li>
            <form id="quicksearch" action="<?= URLHelper::getLink('sem_portal.php', array('send' => 'yes', 'group_by' => '0') + $link_params) ?>" method="post">
              <script>
                var selectSem = function (seminar_id, name) {
                    document.location = "<?= URLHelper::getURL("details.php", array("send_from_search" => 1, "send_from_search_page" => urlencode($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'])."sem_portal.php?keep_result_set=1"))  ?>&sem_id=" + seminar_id;
                };
              </script>
              <?php
              require_once ("lib/classes/QuickSearch.class.php");
              print QuickSearch::get("search_sem_quick_search", new StandardSearch("Seminar_id"))
                    ->setInputClass("quicksearchbox")
                    ->withAttributes(array("title" => sprintf(_('Nach Veranstaltungen suchen (%s)'), htmlready($search_semester_name))))
                    ->setInputStyle("width: 130px; color: #ffffff")
                    ->setDescriptionColor("#e5e5e5")
                    ->fireJSFunctionOnSelect("selectSem")
                    ->noSelectbox()
                    ->render();
              //Komisches Zeugs, das die StmBrowse.class.php braucht:
              print '<input type="hidden" name="search_sem_1508068a50572e5faff81c27f7b3a72f" value="1">';
              //Ende des komischen Zeugs.
              ?>
              <input type="hidden" name="search_sem_sem" value="<?= $search_semester_nr ?>">
              <input class="quicksearchbutton" type="image" src="<?= Assets::url('images/quicksearch_button.png ') ?>" name="search_sem_do_search" value="OK" title="<?= sprintf(_('Nach Veranstaltungen suchen (%s)'), htmlready($search_semester_name)) ?>">
            </form>
            </li>
            <? endif ?>
            <? if (Navigation::hasItem('/links')) : ?>
            <? foreach (Navigation::getItem('/links') as $nav) : ?>
                <? if ($nav->isVisible()) : ?>
                    <li>
                    <a
                    <? if (is_internal_url($url = $nav->getURL())) : ?>
                        href="<?= URLHelper::getLink($url, $link_params) ?>"
                    <? else : ?>
                        href="<?= htmlspecialchars($url) ?>" target="_blank"
                    <? endif ?>
                    >
                    <?= htmlReady($nav->getTitle()) ?>
                    </a>
                    </li>
                <? endif ?>
            <? endforeach ?>
            <? endif ?>
        </ul>
    </div>
</div>
<div id="barBottomshadow">
</div>
<? if (isset($navigation)) : ?>
    <?= $this->render_partial('tabs') ?>
<? endif ?>
<!-- Ende Header -->

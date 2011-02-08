<?
# Lifter010: TODO
?>
<? if (sizeof($navigation)) : ?>
    <ul role="navigation" id="skiplink_list">
    <? $i = 1 ?>
    <? foreach ($navigation as $nav) : ?>
        <li>
        <? if (substr($url = $nav->getURL(), 0, 1) == '#') : ?>
            <a href="<?= $url ?>" onclick="STUDIP.SkipLinks.setActiveTarget('<?= $url ?>');"  tabindex="<?= $i++ ?>"><?= htmlReady($nav->getTitle()) ?></a>
        <? else : ?>
            <? if (is_internal_url($url)) : ?>
                <a href="<?= URLHelper::getLink($url) ?>" tabindex="<?= $i++ ?>"><?= htmlReady($nav->getTitle()) ?></a>
            <? else : ?>
                <a href="<?= htmlspecialchars($url) ?>" tabindex="<?= $i++ ?>"><?= htmlReady($nav->getTitle()) ?></a>
            <? endif ?>
        <? endif ?>
        </li>
    <? endforeach ?>
    </ul>
<? endif ?>
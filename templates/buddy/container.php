<div id="buddycontainer">
    <div class="header">
        <div class="text">Buddies</div>

        <? if ($GLOBALS['auth']->auth['uname'] === $username) : ?>
            <div class="toggles">
                <? if (MayPublishBuddies($GLOBALS['auth']->auth['uid'])) : ?>
                    <a href="dispatch.php/buddies/toggle_publish" title="<?= _("Ihre Buddies werden �ffentlich angezeigt.") ?>">
                        <?= Assets::img('vote-icon-visible.gif', array('alt' => _("Ihre Buddies werden �ffentlich angezeigt."))) ?>
                    </a>
                <? else : ?>
                    <a href="dispatch.php/buddies/toggle_publish" title="<?= _("Ihre Buddies werden nicht �ffentlich angezeigt.") ?>">
                        <?= Assets::img('vote-icon-invisible.gif', array('alt' => _("Ihre Buddies werden nicht �ffentlich angezeigt."))) ?>
                    </a>
                <? endif ?>
            </div>
        <? endif ?>
    </div>

    <? if (!sizeof($buddies)) : ?>
        <div class="minor without_buddies">
            <?= _("Keine Freunde.") ?>
            <? if ($GLOBALS['auth']->auth['uname'] === $username) : ?>
                <a href="contact.php">Finde einen!</a>
            <? endif ?>
        </div>
    <? else : ?>
        <?
        require_once 'lib/classes/Avatar.class.php';
        $limit = ($show_all || sizeof($buddies) <= 15) ? PHP_INT_MAX : 12;
        $buddies_to_show = array_splice($buddies, 0, $limit);
        ?>

        <ul class="buddies" id="buddies-head">
            <? foreach ($buddies_to_show as $id) : ?>
                <li>
                    <a href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>about.php?username=<?= get_username($id) ?>" title="<?= get_fullname($id) ?>">
                        <?= Avatar::getAvatar($id)->getImageTag(Avatar::SMALL) ?>
                    </a>
                </li>
            <? endforeach ?>
            <? if ($num = sizeof($buddies)) : ?>
                <li class="morebuddies">
                    <a href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>about.php?username=<?= $username ?>&amp;show_all_buddies">
                    + <?= $num ?>
                </li>
            <? endif ?>
        </ul>
    <? endif ?>
</div>


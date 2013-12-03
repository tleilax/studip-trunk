<? $user = new User($new['user_id']); ?>

<a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $user->username) ?>">
    <span style="color: #339;">
        <?= htmlReady($user->getFullName()) ?>
    </span>
</a>
<?= date('d.m.Y', $new['date']) ?> |
<span style="color: #050"><?= object_return_views($new['news_id']) ?></span> |

<?
if ($new['allow_comments']):
    $num = StudipComments::NumCommentsForObject($new['news_id']);
    $visited = object_get_visit($new['news_id'], 'news', false, false);
    $isnew = StudipComments::NumCommentsForObjectSinceLastVisit($new['news_id'], $visited, $GLOBALS['user']->id);
    ?>

    <? if ($isnew): ?>
        <span style="color: red;" title="<?= sprintf(_('%s neue(r) Kommentar(e)'), $isnew) ?>">
        <? else: ?>
            <span style="color: #aa6;">
            <? endif; ?>
            <?= $num ?>
        </span> |
    <? endif; ?>

    <? if ($new->havePermission('edit')): ?>
        <a href=" <?= URLHelper::getLink('dispatch.php/news/edit_news/' . $new->id) ?>" rel='get_dialog' >
            <?= Assets::img('icons/16/blue/admin.png'); ?>
        </a>
        <? if ($new->havePermission('unassign', $range)): ?>
            <?= LinkButton::create(_('Entfernen'), URLHelper::getLink('?nremove=' . $news['news_id'] . '#anker'))
            ?>
        <? endif; ?>
        <? if ($new->havePermission('delete')): ?>
            <?= Assets::img('icons/16/blue/trash.png'); ?>
        <? endif; ?>
    <? endif; ?>
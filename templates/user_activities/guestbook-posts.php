<table class="default">
<? foreach ($posts as $post): 
     $user_link = sprintf('<a href="%s">%s</a>',
                          URLHelper::getLink('about.php', array('username' => get_username($post['user_id']))),
                          htmlReady(get_fullname($post['user_id'])));
    $delete_link = URLHelper::getURL('', array('deletepost' => $post['post_id'], 'ticket' => get_ticket()));
?>
    <tbody>
        <tr>
            <td class="steel2" style="font-weight: bold;">
                <?= sprintf(_('%s hat am %s geschrieben:'), $user_link, date('d.m.Y - H:i', $post['mkdate'])) ?>
            </td>
        </tr>
        <tr>
            <td class="steelgraulight">
                <?= formatready($post['content']) ?>
                <p align="right">
                    <?= Studip\LinkButton::create(_('L�schen'), $delete_link) ?>
                </p>
            </td>
        </tr>
        <tr>
            <td class="steel1">&nbsp;</td>
        </tr>
    </tbody>
<? endforeach; ?>
</table>

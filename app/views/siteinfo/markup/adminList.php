<? if ($error) : ?>
    <em><?= $error ?></em>
<? else : ?>
    <? $current_head = "" ?>
    <? $switch_column = count($admins)/2 ?>
    <? $i = 0 ?>
    <table width="100%">
        <tr>
            <td>
        <? foreach($admins as $admin) : ?>
            <? if ($current_head != $admin['institute']) :?>
                <? $current_head = $admin['institute'] ?>
                <? if ($i>$switch_column) : ?>
                    </td>
                    <td>
                    <? $i = 0 ?>
                <? endif ?>
                <h4><?= htmlReady($current_head) ?></h4>
            <? endif ?>
            <a href="<?= URLHelper::getLink('about.php', array('username' => $admin['username'])) ?>">
                <?= htmlReady($admin['fullname']) ?>
            </a>, E-Mail:<?= FixLinks(htmlReady($admin['Email'])) ?><br />
            <? $i++ ?>
        <? endforeach ?>
            </td>
        </tr>
    </table>
<? endif ?>

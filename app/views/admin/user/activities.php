<h1><?= PageLayout::getTitle() ?></h1>
<section class="contentbox">
    <header>
        <h1>
            <?= _('Informationen') ?>
        </h1>
    </header>
    <table class="default">

        <? foreach ($queries as $query): ?>
            <tr>
                <td style="font-weight: bold;"><?= $query['desc'] ?></td>
                <td <? if (!$query['value']) echo 'style="color:#888;"'; ?>>
                    <?= htmlReady($query['value']) ?>
                </td>
                <td width="1%">
                    <? if ($query['details']): ?>
                        <a href="<?= URLHelper::getLink('?' . $query['details']) ?>">
                            <?= Icon::create('infopage', 'clickable', ['title' => _('Details anzeigen')])->asImg() ?>
                        </a>
                    <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
    </table>
</section>

<?= $this->render_partial('admin/user/_activity_details.php'); ?>
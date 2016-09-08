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
            </tr>
        <? endforeach; ?>
    </table>
</section>

<?= $this->render_partial('admin/user/_activity_details.php'); ?>
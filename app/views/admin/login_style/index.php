<?php if (count($pictures) > 0) : ?>
    <table class="default">
        <caption>
            <?= _('Hintergrundbilder f�r den Startbildschirm') ?>
        </caption>
        <colgroup>
            <col>
            <col width="400">
            <col width="100">
            <col width="25">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Info') ?></th>
                <th><?= _('Vorschau') ?></th>
                <th><?= _('Aktiviert f�r') ?></th>
                <th><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <?php foreach ($pictures as $pic) : $dim = $pic->getDimensions(); ?>
            <tr>
                <td>
                    <?= htmlReady($pic->filename) ?>
                    <br>
                    (<?= $dim[0] ?> x <?= $dim[1] ?>,
                    <?= relsize($pic->getFilesize(), false) ?>)
                </td>
                <td>
                    <img src="<?= $pic->getURL() ?>" width="400">
                </td>
                <td>
                    <a href="<?= $controller->url_for('admin/loginstyle/activation', $pic->id, 'desktop', (int) !$pic->desktop) ?>">
                        <?= Icon::create('computer', $pic->desktop ? 'clickable' : 'inactive', [
                            'title' => $pic->desktop
                                     ? _('Bild nicht mehr f�r die Desktopansicht verwenden')
                                     : _('Bild f�r die Desktopansicht verwenden')
                        ])->asImg(32) ?>
                    </a>
                    <a href="<?= $controller->url_for('admin/loginstyle/activation', $pic->id, 'mobile', (int) !$pic->mobile) ?>">
                        <?= Icon::create('cellphone', $pic->mobile ? 'clickable' : 'inactive', [
                            'title' => $pic->mobile
                                     ? _('Bild nicht mehr f�r die Mobilansicht verwenden')
                                     : _('Bild f�r die Mobilansicht verwenden')
                        ])->asImg(32) ?>
                    </a>
                </td>
                <td>
                <?php if (!$pic->in_release): ?>
                    <a href="<?= $controller->url_for('admin/loginstyle/delete', $pic->id) ?>">
                        <?= Icon::create('trash', 'clickable', ['title' => _('Bild l�schen')]) ?>
                    </a>
                <?php endif; ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
<?php else : ?>
    <?= PageLayout::postInfo(_('In Ihrem System sind leider keine Bilder f�r den Startbildschirm hinterlegt.')) ?>
<?php endif ?>

<? foreach ($entries as $area): ?>
<ul style="margin: 0;">
    <li data-id="<?= $area['topic_id'] ?>">
        <? if ($area['content_raw']) : ?>
        <a class="tooltip2">
            <?= Icon::create('info-circle', 'inactive')->asImg(16, ['class' => 'text-top']) ?>
            <span><?= nl2br(htmlReady($area['content_raw'])) ?></span>
        </a>
        <? endif ?>

        <? if ($area['depth'] < 3) : ?>
        <a href="javascript:STUDIP.Forum.adminLoadChilds('<?= $area['topic_id'] ?>')"><?= htmlReady($area['name_raw']) ?></a>
        <? else : ?>
        <?= htmlReady($area['name_raw']) ?>
        <? endif ?>

        <a href="javascript:STUDIP.Forum.cut('<?= $area['topic_id'] ?>');" data-role="cut">
        <?= Icon::create('export', 'clickable')->asImg(16) ?>
        </a>


        <a href="javascript:STUDIP.Forum.cancelCut('<?= $area['topic_id'] ?>');" data-role="cancel_cut" style="display: none">
        <?= Icon::create('export', 'attention')->asImg(16) ?>
        </a>

        <a href="javascript:STUDIP.Forum.paste('<?= $area['topic_id'] ?>');" data-role="paste" style="display: none">
        <?= Icon::create('arr_2left', 'sort')->asImg(16) ?>
        </a>
    </li>
</ul>
<? endforeach ?>
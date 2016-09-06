<h1>STUB File view</h1>
<ul>
<?
//for early development stage we MUST check which class is available:
if(class_exists('StudipDocument')) : ?>
<? foreach ($files as $file) : ?>
<li>
    <?= htmlReady($file->name) . ' (' . htmlReady($file->filename) . ')' ?>
    <a href="<?= URLHelper::getLink('sendfile.php', [
            'file_id' => $file->id,
            'force_download' => '1',
            'type' => '6',
            'file_name' => $file->filename
        ]) ?>"
        data-dialog="reload-on-close">
        <?= Icon::create('download', 'clickable')->asImg('12px') ?>
    </a>
    <a href="<?= URLHelper::getLink('dispatch.php/file/edit', ['fileId' => $file->id]) ?>"
        data-dialog="reload-on-close">
        <?= Icon::create('edit', 'clickable')->asImg('12px') ?>
    </a>
    <a href="<?= URLHelper::getLink('dispatch.php/file/link', ['fileId' => $file->id]) ?>"
        data-dialog="reload-on-close">
        <?= Icon::create('group', 'clickable')->asImg('12px') ?>
    </a>
    <a href="<?= URLHelper::getLink('dispatch.php/file/delete', ['fileId' => $file->id]) ?>"
        data-dialog="reload-on-close">
        <?= Icon::create('trash', 'clickable')->asImg('12px') ?>
    </a>
</li>
<? endforeach ?>    
<? elseif(class_exists('File')) : ?>
<? foreach ($files as $file) : ?>
<li><?= 'TO BE DESIGNED' ?></li>
<? endforeach ?>
<? endif ?>
</ul>
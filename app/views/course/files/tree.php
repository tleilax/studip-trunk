<h1>STUB File view</h1>
<h2>
    <?foreach ($topFolder->getParents() as $parent) : ?>
        <a href="<?=$controller->link_for('/tree/' . $parent->id)?>">
            <?=htmlReady($parent->name)?> /
        </a>
    <?endforeach;?>
</h2>
<ul>

<? foreach ($topFolder->subfolders as $folder) : ?>
<li>
    <div><a href="<?=$controller->link_for('/tree/' . $folder->id)?>"><?= htmlready($folder->name)?></a></div>
 </li>
<? endforeach ?>
    <ul>
    <? foreach ($topFolder->file_refs as $fileref) : ?>
        <li>
        <div>
            <a href="<?=$fileref->getDownloadURL()?>"><?=htmlReady($fileref->file->name)?></a>
            <a href="<?= URLHelper::getLink('dispatch.php/file/edit', ['fileId' => $fileref->id]) ?>"
               data-dialog="reload-on-close">
                <?= Icon::create('edit', 'clickable')->asImg('12px') ?>
            </a>
            <a href="<?= URLHelper::getLink('dispatch.php/file/link', ['fileId' => $fileref->id]) ?>"
               data-dialog="reload-on-close">
                <?= Icon::create('group', 'clickable')->asImg('12px') ?>
            </a>
            <a href="<?= URLHelper::getLink('dispatch.php/file/delete', ['fileId' => $fileref->id]) ?>"
               data-dialog="reload-on-close">
                <?= Icon::create('trash', 'clickable')->asImg('12px') ?>
            </a>
        </div>
        </li>
    <? endforeach ?>
    </ul>
</ul>
<style type="text/x-less">
@delay: 300ms;

.hide-text() {
    text-indent: 100%;
    white-space: nowrap;
    overflow: hidden;
}
.studip-icon(@icon) {
    background-image: url(@icon);
    background-repeat: no-repeat;
    background-position: center;
    display: inline-block;
    height: 16px;
    width: 16px;
}
.file-tree {
    &, ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    ul {
        margin-left: 20px;
    }
    li {
        overflow: hidden;
        &.empty-directory:before {
            content: ' ';
            .studip-icon('<?= Assets::image_path('icons/16/blue/folder-empty.png') ?>');
        }
    }
    input[type=checkbox] {
        display: none;
        + label {
            .hide-text;
            .studip-icon('<?= Assets::image_path('icons/16/blue/arr_1right.png') ?>');
            cursor: pointer;
            
            transition: transform @delay;
        }
        ~ ul {
            max-height: 0;
            opacity: 0;
            
            transition: max-height @delay, opacity @delay;
        }

        &:checked {
            + label {
                transform: rotate(90deg);
            }
            ~ ul {
                max-height: 1000px;
                opacity: 1;
            }
        }
    }
    input[type=radio] {
        display: none;
        
        + label {
            cursor: pointer;
            &:before {
                content: ' ';
                .studip-icon('<?= Assets::image_path('icons/16/blue/checkbox-unchecked.png') ?>');
                opacity: 0.5;
                margin-right: 8px;
            }
        }
        
        &:checked + label {
            font-weight: bold;
            &:before {
                background-image: url(<?= Assets::image_path('icons/16/blue/checkbox-checked.png') ?>);
                opacity: 1;
            }
        }
    }
    
}
</style>
<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/1.6.3/less.min.js"></script>

<form action="<?= $controller->url_for('document/files/move/'. $file_id . '/' . $parent_id) ?>" method="post">
<? if ($file_id === 'flashed'): ?>
<? foreach ($flashed as $id): ?>
    <input type="hidden" name="file_id[]" value="<?= $id ?>">
<? endforeach; ?>
<? endif; ?>

    <ul class="file-tree">
        <li class="file-directory">
            <input type="radio" name="folder_id" id="folder-<?= $context_id ?>"
                   value="<?= $context_id ?>" <? if ($context_id === $parent_file_id) echo 'checked'; ?>>
            <label for="folder-<?= $context_id ?>"><?= _('Hauptverzeichnis') ?></label>
            <?= $this->render_partial('document/dir-tree.php', array('children' => $dir_tree)) ?>
        </li>
    </ul>
    
    <?= Studip\Button::createAccept(_('Verschieben'), 'move') ?>
    <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('document/files/index/' . $parent_id)) ?>
</form>
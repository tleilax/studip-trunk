<div class="modalshadow">
    <div class="messagebox messagebox_modal">
        <?= formatReady(_("M�chten Sie folgende Inhaltselemente wirklich deaktivieren? Vorhandene Inhalte werden in der Regel dabei gel�scht")
                    . "\n" . $deactivate_modules_names) ?>
        <div style="margin-top: 1em;">
            <form action="<?= $controller->url_for('course/studygroup/update/'.$sem_id) ?>" method=post>
                <?= CSRFProtection::tokenTag() ?>
                <? foreach($this->flash['deactivate_modules'] as $module) :?>
                     <input type="hidden" name="deactivate_modules[]" value="<?=$module?>">
                <? endforeach ?>
                <? foreach($this->flash['deactivate_plugins'] as $plugin) :?>
                     <input type="hidden" name="deactivate_plugins[]" value="<?=$plugin?>">
                <? endforeach ?>
          
                <input type="hidden" name="really_deactivate" value="1">
                <?= makeButton('ja', 'input') ?>
                <a href="<?= $controller->url_for('course/studygroup/update/'.$sem_id . '?abort_deactivate=1') ?>" style="margin-left: 1em;">
                   <?= makebutton('nein') ?>
                </a>
            </form>
        </div>
    </div>
</div>

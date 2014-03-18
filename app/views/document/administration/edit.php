<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<? use Studip\Button, Studip\LinkButton; ?>
<form action="<?= $controller->url_for('document/administration/store/'.$config_id.'/'.$isGroupConfig)?>"class="studip_form">
    <? if(isset($head)) : ?>
        <h3><?=$head?></h3>
    <? endif; ?>
        <? if($config_id == 0) : ?>
            <? if ($isGroupConfig == true) : ?>
                <fieldset>
                    <legend><?= _('Nutzergruppe:') ?></legend>
                    <select name="group" id="group">
                        <? foreach ($groups as $group) : ?>
                            <option value="<?= $group ?>"><?= $group ?></option>
                        <? endforeach; ?>
                    </select>
                </fieldset>
            <?elseif ($isGroupConfig == false) : ?>
                <input type="hidden" name="group" id="group" value="<?=$user_id?>">
            <? endif; ?>
        <?endif;?>
        <fieldset>
            <legend><?=_('Maximaler Upload: ')?></legend>
            <table>
                <tr>
                    <td>
                        <input type="number" name="upload_size" id="upload_size" 
                            <? if(isset($config['upload_quota'])) : ?>
                               value="<?=$config['upload_quota']?>"
                            <? else : ?>
                                value="0"
                            <? endif; ?>
                        >
                    </td>
                    <td>
                        <select name ="unitUpload">
                             <? foreach(array( 'kB','MB','GB','TB') as $unit) : ?>
                        <option value="<?= $unit ?>"
                            <?if($unit=='MB' || (count($config) > 0 && $unit==$config['upload_unit'])) :?>
                                selected <?endif;?>><?= $unit ?></option>
                    <? endforeach ?>
                        </select>
                    </td>
                </tr>
            </table>
        </fieldset>
        
        <fieldset>
            <legend><?=_('Maximales Quota : ')?></legend>
            <table>
                <tr>
                    <td>
                        <input type="number" name="quota_size" id="upload_size"
                            <? if(isset($config['quota'])) : ?>
                               value="<?=$config['quota']?>"
                            <? else : ?>
                                value="0"
                            <? endif; ?>
                        >
                    </td>
                    <td>
                        <select name="unitQuota" id="unitQuota">
                             <? foreach(array( 'kB','MB','GB','TB') as $unit) : ?>
                        <option value="<?= $unit ?>"
                            <?if($unit=='MB' || (count($config) > 0 && $unit == $config['quota_unit'])) :?>
                                selected <?endif;?>><?= $unit ?></option>
                    <? endforeach ?>
                        </select>
                    </td>
                </tr>
            </table>
        </fieldset>
        
        <fieldset>
            <legend><?=_('Untersagte Dateitypen: ')?></legend>
            <table>
                <tr>
                    <td>
                        <select id="datetype" multiple="multiple" name="datetype[]" style="height: 40%; width: 90%">
                            <? foreach ($types as $type) : ?>
                                <?
                                foreach ($this->config['types'] as $forbiddenTypes) : ?>
                                    <? if ($forbiddenTypes['id'] == $type['id']) : ?>
                                        <? $setAs = 'selected'; ?>
                                    <?endif;?>
                                <? endforeach; ?>                  
                                <option value="<?= $type['id'] ?>"<?= $setAs ?>><?= $type['type'] ?></option>
                                <? $setAs = '' ?>
                                <? endforeach ?>
                        </select>
                    </td>
                </tr>
            </table>
        </fieldset>
        <? if($config_id != 0) : ?>
            <?= Button::create(_('Übernehmen'),'store') ?>
        <? else : ?>
            <?= Button::create(_('Speichern'),'store') ?>
        <?endif;?>
        <?= LinkButton::create(_('Abbrechen'), $controller->url_for('document/administration/filter')) ?>
</form>

<script type="text/javascript">
    $(function(){
        // or disable some features
        $("#datetype").multiselect({sortable: false, searchable: true});
    });
</script>
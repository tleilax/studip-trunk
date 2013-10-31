<?use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->url_for('document/administration/store') ?>" method="post">
<table class="default">
    <colgroup>
        <col width="15%">
        <col width="30%">
        <col width="30%">
        <col width="25%">
    </colgroup>
    <thead>
        <tr>
            <th colspan="4"><?= _('Standardeinstellungen anlegen/bearbeiten') ?></th>            
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <label><?= _('Nutzergruppe')?></label><br>
                <select id="usergroup "name="usergroup">
                    <? foreach(array("default", "user", "autor", "tutor", "dozent", "admin", "root") as $one) : ?>
                <option value="<?= $one ?>" <?if(count($this->viewData['configEdit'])>0 && $one == $this->viewData['configEdit']['name']){echo 'selected';}?>><?= $one ?></option>
            <? endforeach ?>
            </select>
                </select>
            </td>
            <td>
                <label><?=_('Max. Uploadgröße')?></label><br>
                <input id="upload" name="upload" value="<? if(count($this->viewData['configEdit'])>0 ){echo $this->viewData['configEdit']['upload'];}else{echo '0';}?>">
                <select id="unitUpload" name="unitUpload" >
                     <? foreach(array( 'kB','MB','GB','TB') as $unit) : ?>
                        <option value="<?= $unit ?>"<?if($unit=='MB' || (count($this->viewData['configEdit'])>0 && $unit==$this->viewData['configEdit']['upload_unit'])){echo 'selected';}?>><?= $unit ?></option>
                    <? endforeach ?>
                </select>
            </td>
            <td>
                <label><?=_('Nutzerquota')?></label><br>
                <input id="quota" name="quota" value="<? if(count($this->viewData['configEdit'])>0 ){echo $this->viewData['configEdit']['quota'];}else{echo '0';}?>">
                <select id="unitQuota" name="unitQuota">
                    <? foreach(array( 'kB','MB','GB','TB') as $unitQuota) : ?>
                        <option value="<?= $unitQuota ?>"<?if($unitQuota=='MB' || (count($this->viewData['configEdit'])>0 && $unit==$this->viewData['configEdit']['quota_unit'])){echo 'selected';}?>><?= $unitQuota ?></option>
                    <? endforeach ?>
                </select>
            </td>
            <td>
                <label><?=_('Upload untersagen')?></label><br>
                <input type="checkbox" id="forbidden" name="forbidden" <?if(count($this->viewData['configEdit'])>0 && $this->viewData['configEdit']['forbidden']==1){echo 'checked';}?>>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <label><?=_('Dateitypen ausschließen')?></label>
                <select id="datetype" multiple="multiple" name="datetype[]" style="height: 25%; width: 60%">
                    <? foreach($this->viewData['types'] as $types) : ?>
                            <?foreach($this->viewData['configEdit']['types'] as $forbiddenTypes)
                                if($forbiddenTypes == $types){
                                    $setAs = 'selected';
                                }
                            ?>                  
                    
                        <option value="<?=$types['id'] ?>"<?=$setAs?>><?= $types['type'] ?></option>
                        <?$setAs = ''?>
                    <? endforeach ?>
                </select>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>
<?= Button::createAccept(_('Übernehmen'),'strore') ?>
            </td>    
        </tr>
    </tfoot>
</table>    

<table class="default zebra-hover cronjobs">
    <colgroup>
        <col width="15%">
        <col width="17%">
        <col width="17%">
        <col width="30%">
        <col width="10%">
        <col width="5%">
        <col width="5%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Nutzergruppe') ?></th>
            <th><?= _('Max. Uploadgröße') ?></th>
            <th><?= _('Nutzerquota') ?></th>
            <th><?= _('Untersagte Dateitypen') ?></th>
            <th><?= _('Upload untersagt') ?></th>
            <th colspan="2"><?= _('Aktion') ?></th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach($this->viewData['configAll'] as $conf):?>
            <tr>
                    <td><?=$conf['name']?></td>
                    <td><?=$conf['upload']?> <?=$conf['upload_unit']?></td>
                    <td><?=$conf['quota']?> <?=$conf['quota_unit']?></td>
                    <td>
                        <?foreach($conf['types'] as $type):?>
                            <?=$type['type'].' '?>
                        <? endforeach;?>
                        <?if($conf['forbidden']=='1'):?>
                            </td><td> <input type="checkbox" name="box" checked disabled></td>
                        <?else :?>
                            </td><td> <input type="checkbox" name="box" disabled></td>
                        <?  endif;?>
            <?if($conf['name']!='default'):?>
                <td><a href="<?=$controller->url_for('document/administration/delete/'.$conf['name'].'/groupConfig')?>"><?=Assets::img('icons/16/blue/trash.png')?></a></td>
                <td><a href="<?=$controller->url_for('document/administration/index/'.$conf['name'])?>"><?=Assets::img('icons/16/blue/edit.png')?></a></td>
                </tr>
            <?else : ?>
                <td></td>
                <td><a href="<?=$controller->url_for('document/administration/index/'.$conf['name'])?>"><?=Assets::img('icons/16/blue/edit.png')?></a></td>
                </tr>
            <?  endif;?>            
        <?endforeach;?>
    </tbody>
    <tfoot>        
    </tfoot>
</table>
</form>
<!--Java Script fuer die Multiselectbox-->
<script type="text/javascript">

    $(function(){
        // or disable some features
        $("#datetype").multiselect({sortable: false, searchable: true});
    });
</script>
<!--Java Script fuer die Multiselectbox ENDE-->


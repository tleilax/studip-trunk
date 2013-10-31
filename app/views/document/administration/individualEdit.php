<?use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->url_for('document/administration/storeIndividual').'/'.$this->viewData['userInfo']['user']['user_id'] ?>" method="post">
    <table class="default">
    <colgroup>
        <col width="15%">
        <col width="30%">
        <col width="30%">
        <col width="25%">
    </colgroup>
    <thead>
        <tr>
            <th colspan="4"><?=_('Individuelle-Einstellungen für: ')?><?= $this->viewData['userInfo']['user']['Vorname'].' '.$this->viewData['userInfo']['user']['Nachname'].' ('.$this->viewData['userInfo']['user']['perms'].')'?></th>            
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <label><?=_('Max. Uploadgröße')?></label><br>
                <input id="upload" name="upload" value="<? if(count($this->viewData['userConfig'])>0 ){echo $this->viewData['userConfig']['upload'];}else{echo '0';}?>">
                <select id="unitUpload" name="unitUpload" >
                     <? foreach(array( 'kB','MB','GB','TB') as $unit) : ?>
                        <option value="<?= $unit ?>"<?if($unit=='MB' || (count($this->viewData['userConfig'])>0 && $unit==$this->viewData['userConfig']['upload_unit'])){echo 'selected';}?>><?= $unit ?></option>
                    <? endforeach ?>
                </select>
            </td>
            <td>
                <label><?=_('Nutzerquota')?></label><br>
                <input id="quota" name="quota" value="<? if(count($this->viewData['userConfig'])>0 ){echo $this->viewData['userConfig']['quota'];}else{echo '0';}?>">
                <select id="unitQuota" name="unitQuota">
                    <? foreach(array( 'kB','MB','GB','TB') as $unitQuota) : ?>
                        <option value="<?= $unitQuota ?>"<?if($unitQuota=='MB' || (count($this->viewData['userConfig'])>0 && $unit==$this->viewData['userConfig']['quota_unit'])){echo 'selected';}?>><?= $unitQuota ?></option>
                    <? endforeach ?>
                </select>
            </td>
            <td colspan="2">
                <label><?=_('Upload untersagen')?></label><br>
                <input type="checkbox" id="forbidden" name="forbidden" <?if(count($this->viewData['userConfig'])>0 && $this->viewData['userConfig']['forbidden']==1){echo 'checked';}?>>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <label><?=_('Dateitypen ausschließen')?></label>
                <select id="datetype" multiple="multiple" name="datetype[]" style="height: 25%; width: 60%">
                    <? foreach($this->viewData['types'] as $types) : ?>
                            <?foreach($this->viewData['userConfig']['types'] as $forbiddenTypes)
                                if($forbiddenTypes == $types){
                                    $setAs = 'selected';
                                }
                            ?>                  
                    
                        <option value="<?=$types['id'] ?>"<?=$setAs?>><?= $types['type'] ?></option>
                        <?$setAs = ''?>
                    <? endforeach ?>
                </select>
        </tr>
        <tr>
            <td colspan="4">
             <label><?=_('Dateibereich sperren')?></label><br>
                <input type="checkbox" id="close" name="close" <?if(count($this->viewData['userConfig'])>0 && $this->viewData['userConfig']['area_close']==1){echo 'checked';}?>>   
            </td>
            </tr>
            <tr>
            <td colspan="4">
            <textarea name="closeText" cols="50" rows="10" style="resize: none;" align="left" >
            <?
            if(count($this->viewData['userConfig'])>0 && strlen($this->viewData['userConfig']['area_close_text'])>1){
                echo trim($this->viewData['userConfig']['area_close_text']);
            }
            ?>
            </textarea>  
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>
<?= LinkButton::create(_('Abbrechen'), $controller->url_for('document/administration') . '/individual') ?>
<?= Button::createAccept(_('Übernehmen'),'strore') ?>
<?= LinkButton::createCancel(_('Einstellungen löschen'), $controller->url_for('document/administration') . '/delete/'.$this->viewData['userInfo']['user']['user_id'].'/userConfig') ?>
            </td>    
        </tr>
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



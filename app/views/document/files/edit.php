   <table>
    <tr>
     <td style="vertical-align:top;">
      <?php

       print Assets::img('icons/48/blue/edit.png');
       print '<b>'. _('Upload-Ordner:'). '</b>';
       print $env_dirname;
       print '<b>'. _('Autor/in'). '</b>';
       print $realname;

      ?>

     </td>
     <td style="vertical-align:top; padding-left:15px;">
     </td>
     <td style="vertical-align:top; padding-left:15px; width:72%;">
      <table>
       <tbody>
        <tr>
         <td style="border-bottom:0px;">
          <form method="post" action="<?= $controller->url_for('document/files/edit/' . $entry->id) ?>">

           <?= CSRFProtection::tokenTag() ?>
           
           <p>
               <input type="text" name="filename" placeholder="<?= _('Dateiname') ?>" value="<?= htmlReady($entry->getFile()->filename) ?>" required>
           </p>
           <p>
            <input type="text" name="name" placeholder="<?= _('Titel') ?>" value="<?= htmlReady($entry->getName()) ?>" required>
           </p>
           <p>
            <textarea cols="38" rows="4" name="description" placeholder="<?= _('Beschreibung') ?>"><?= htmlReady($entry->getDescription()) ?></textarea>
           </p>
           <p>
            <input type="radio" name="restricted" value="0" <? if (!$entry->getFile()->restricted) echo 'checked'; ?>>
            <?= _('Ja, dieses Dokument ist frei von Rechten Dritter.') ?>
           </p>
           <p>
            <input type="radio" name="restricted" value="1" <? if ($entry->getFile()->restricted) echo 'checked'; ?>>

            <?= _('Nein, dieses Dokument ist <u>nicht</u> frei von Rechten Dritter.') ?>
           </p>
           <?= Studip\Button::createAccept(_('Bearbeiten')) ?>
           <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('document/files/index/' . $controller->getParentId($entry->id))) ?>
          </form>
         </td>
        </tr>
       </tbody>
      </table>
     </td>
    </tr>
   </table>


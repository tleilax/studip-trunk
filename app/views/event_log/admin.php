<div class="white" style="padding: 1ex;">

  <? if (isset($error_msg)): ?>
    <?= Messagebox::error($error_msg) ?>
  <? endif ?>

  <h3><?= _('Konfiguration der Logging-Funktionen') ?></h3>

  <p><?= _('Sie k�nnen hier einen Teil der Logging-Funktionen direkt ver�ndern.') ?></p>

  <form action="<?= $controller->url_for('event_log/save/'.urlencode($edit_id)) ?>" method="POST">

    <table cellpadding="3" style="border-collapse: collapse; width: 100%;">
      <tr>
        <th style="text-align: left;">
          <?= _('Name') ?>
        </th>
        <th style="text-align: left;">
          <?= _('Beschreibung') ?>
        </th>
        <th style="text-align: left;">
          <?= _('Template') ?>
        </th>
        <th style="text-align: left;">
          <?= _('Anzahl') ?>
        </th>
        <th style="text-align: left;">
          <?= _('Aktiv?') ?>
        </th>
        <th style="text-align: left;">
          <?= _('Ablaufzeit') ?>
        </th>
        <th style="text-align: left;">
        </th>
      </tr>

      <? foreach ($log_actions as $log_action): ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
          <td style="font-size: smaller;">
            <?= htmlReady($log_action['name']) ?>
          </td>
          <? if ($edit_id === $log_action['action_id']): ?>
            <td style="font-size: smaller;">
              <a name="edit"></a>
              <input type="text" style="width: 100%;" name="description" value="<?= htmlReady($log_action['description']) ?>">
            </td>
            <td style="font-size: smaller;">
              <input type="text" style="width: 100%;" name="info_template" value="<?= htmlReady($log_action['info_template']) ?>">
            </td>
            <td style="font-size: smaller;">
              <?= $log_action['log_count'] ?>
            </td>
            <td style="font-size: smaller;">
              <input type="checkbox" name="active" value="1" <?= $log_action['active'] ? 'checked' : '' ?>>
            </td>
            <td style="font-size: smaller; white-space: nowrap;">
              <input type="text" style="width: 4ex;" name="expires"
                     value="<?= $log_action['expires'] / 86400 ?>"
                     title="<?= _('0 = keine Ablaufzeit') ?>"> <?= _('Tage') ?>
            </td>
            <td style="font-size: smaller;">
              <input type="image" name="save" src="<?= Assets::image_path('haken_transparent.gif') ?>">
            </td>
          <? else: ?>
            <td style="font-size: smaller;">
              <?= htmlReady($log_action['description']) ?>
            </td>
            <td style="font-size: smaller;">
              <?= htmlReady($log_action['info_template']) ?>
            </td>
            <td style="font-size: smaller;">
              <?= $log_action['log_count'] ?>
            </td>
            <td style="font-size: smaller;">
              <? if ($log_action['active']): ?>
                <?= Assets::img('haken_transparent.gif') ?>
              <? else: ?>
                <?= Assets::img('x_transparent.gif') ?>
              <? endif ?>
            </td>
            <td style="font-size: smaller; white-space: nowrap;">
              <? if ($log_action['expires'] > 0): ?>
                <?= $log_action['expires'] / 86400 ?> <?= _('Tage') ?>
              <? else: ?>
                <?= Assets::img('x_transparent.gif') ?>
              <? endif ?>
            </td>
            <td style="font-size: smaller;">
              <a href="<?= $controller->url_for('event_log/edit/'.$log_action['action_id']) ?>#edit">
                <?= Assets::img('edit_transparent.gif') ?>
              </a>
            </td>
          <? endif ?>
        </tr>
      <? endforeach ?>
    </table>

  </form>

</div>

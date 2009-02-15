<div class="white" style="padding: 1ex;">

  <h3><?= _('Anzeige der Log-Events') ?></h3>

  <form action="<?= $controller->url_for('event_log/show') ?>" method="POST">

    <p style="font-size: smaller;">
      <select name="action_id">
        <option value="all"><?= _('Alle Aktionen') ?></option>
        <? foreach ($log_actions as $log_action): ?>
          <option value="<?= $log_action['action_id'] ?>"
          <? if ($log_action['action_id'] === $action_id): ?>
            selected
          <? endif ?>
          <? if ($log_action['log_group'] !== $lastgroup): ?>
            <? $lastgroup = $log_action['log_group'] ?>
            style="border-top: 1px solid #cccccc;"
          <? endif ?>
          >
            <?= htmlReady($log_action['description']) ?>
          </option>
        <? endforeach ?>
      </select>

      <?= _('f�r') ?>

      <? if (isset($objects)): ?>
        <? foreach ($types as $name => $title): ?>
          <? if ($type === $name): ?>
            <?= htmlReady($title) ?>
          <? endif ?>
        <? endforeach ?>

        <input type="hidden" name="type" value="<?= htmlReady($type) ?>">
        <input type="hidden" name="search" value="<?= htmlReady($search) ?>">

        <select name="object_id">
          <? foreach ($objects as $object): ?>
            <? $selected = $object[0] === $object_id ? 'selected' : '' ?>
            <option value="<?= $object[0] ?>" <?= $selected ?>><?= htmlReady($object[1]) ?></option>
          <? endforeach ?>
        </select>

        <a href="<?= $controller->url_for('event_log/show?action_id='.urlencode($action_id)) ?>">
          <?= Assets::img('rewind.gif', array('title' => _('neue Suche'))) ?>
        </a>
      <? else: ?>
        <select name="type">
          <? foreach ($types as $name => $title): ?>
            <option value="<?= $name ?>"><?= htmlReady($title) ?></option>
          <? endforeach ?>
        </select>

        <input type="text" size="20" name="search">
      <? endif ?>

      <?= _('in') ?>

      <select name="format">
        <option value="compact"><?= _('Kompaktdarstellung') ?></option>
        <option value="detail"><?= _('Detaildarstellung') ?></option>
      </select>

      &nbsp;
      <?= makeButton('anzeigen', 'input') ?>
    </p>

    <? if (isset($error_msg)): ?>
      <table style="width: 100%;">
        <? my_error($error_msg, '', 1, false, true) ?>
      </table>
    <? endif ?>

    <? if (isset($log_events)): ?>
      <table cellpadding="4" style="border-collapse: collapse; width: 100%;">
        <tr>
          <th style="text-align: left;">
            <?= _('Zeit') ?>
          </th>
          <th style="text-align: left;">
            <?= _('Info') ?>
          </th>
        </tr>

        <? foreach ($log_events as $log_event): ?>
          <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
            <td style="font-size: smaller; white-space: nowrap;">
              <?= date('d.m.Y H:i:s', $log_event['time']) ?>
            </td>
            <td style="font-size: smaller;">
              <?= $log_event['info'] ?>
              <? if ($format === 'detail' && $log_event['detail']): ?>
                <br><?= _('Info:').' '.$log_event['detail'] ?>
              <? endif ?>
              <? if ($format === 'detail' && $log_event['debug']): ?>
                <br><?= _('Debug:').' '.$log_event['debug'] ?>
              <? endif ?>
            </td>
          </tr>
        <? endforeach ?>
      </table>

      <p>
        <? if (count($log_events) > 0): ?>
          <?= sprintf(_('Eintrag %s - %s von %s'), $start + 1, $start + count($log_events), $num_entries) ?>

          <input type="hidden" name="start" value="<?= $start ?>">

          <? if ($start > 0): ?>
            <?= makeButton('zurueck', 'input', false, 'back') ?>
          <? endif ?>
          <? if ($start + count($log_events) < $num_entries): ?>
            <?= makeButton('weiter', 'input', false, 'forward') ?>
          <? endif ?>
      <? else: ?>
        <?= _('keine Eintr�ge gefunden') ?>
      <? endif ?>
      </p>
    <? endif ?>

  </form>

</div>

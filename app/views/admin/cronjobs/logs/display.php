<dl class="cronlog">
    <dt><?= _('Cronjob') ?></dt>
    <dd><?= htmlReady($log->schedule->title) ?></dd>

    <dt><?= _('Aufgabe') ?></dt>
    <dd><?= htmlReady($log->schedule->task->name) ?></dd>

    <dt><?= _('Geplante Ausf�hrung') ?></dt>
    <dd><?= date('d.m.Y H:i:s', $log->scheduled) ?></dd>

    <dt><?= _('Tats�chliche Ausf�hrung') ?></dt>
    <dd><?= date('d.m.Y H:i:s', $log->executed) ?></dd>

    <dt><?= _('Ausf�hrungsdauer') ?></dt>
    <dd><?= number_format($log->duration, 6, ',', '.') ?></dd>

<? if (!empty($log->output)): ?>
    <dt><?= _('Ausgabe') ?></dt>
    <dd><pre><?= htmlReady($log->output) ?></pre></dd>
<? endif; ?>

<? if ($log->exception !== null): ?>
    <dt><?= _('Fehler') ?></dt>
    <dd><?= display_exception($log->exception, true) ?></dd>
<? endif; ?>
</dl>

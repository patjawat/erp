<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\helpdesk2\models\Helpdesk;

/** @var yii\web\View $this */
/** @var array<string, Helpdesk[]> $columns */

$this->title = 'Ticket Kanban Board';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('@web/libs/sortablejs/Sortable.min.js', ['depends' => [\yii\web\JqueryAsset::class]]); // self-hosted (เดิม jsdelivr)

$updateUrl = Url::to(['/helpdesk/service/update-status']);

$js = <<<JS
function initKanban() {
  const statusMap = {
    'kanban-open': 'pending',
    'kanban-in-progress': 'in_progress',
    'kanban-pending': 'pending',
    'kanban-resolved': 'success',
    'kanban-closed': 'cancel'
  };

  document.querySelectorAll('.kanban-column-body').forEach(function (el) {
    new Sortable(el, {
      group: 'tickets',
      animation: 150,
      onEnd: function (evt) {
        const itemEl = evt.item;
        const ticketId = itemEl.getAttribute('data-ticket-id');
        const columnEl = evt.to;
        const columnId = columnEl.getAttribute('id');
        const newStatus = statusMap[columnId] || null;
        if (!ticketId || !newStatus) {
          return;
        }

        $.ajax({
          type: 'POST',
          url: '$updateUrl',
          data: {
            id: ticketId,
            status: newStatus
          },
          dataType: 'json'
        });
      }
    });
  });
}

initKanban();
JS;

$this->registerJs($js);
?>

<div class="row g-3">
    <?php
    $config = [
        'open' => ['label' => 'Open', 'id' => 'kanban-open', 'bg' => 'bg-primary bg-opacity-10', 'badge' => 'bg-primary'],
        'in_progress' => ['label' => 'In Progress', 'id' => 'kanban-in-progress', 'bg' => 'bg-warning bg-opacity-10', 'badge' => 'bg-warning'],
        'pending' => ['label' => 'Pending', 'id' => 'kanban-pending', 'bg' => 'bg-secondary bg-opacity-10', 'badge' => 'bg-secondary'],
        'resolved' => ['label' => 'Resolved', 'id' => 'kanban-resolved', 'bg' => 'bg-success bg-opacity-10', 'badge' => 'bg-success'],
        'closed' => ['label' => 'Closed', 'id' => 'kanban-closed', 'bg' => 'bg-dark bg-opacity-10', 'badge' => 'bg-dark'],
    ];

    foreach ($config as $key => $meta):
        $tickets = $columns[$key] ?? [];
    ?>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="erp-icon-box <?= $meta['bg'] ?>">
                            <i class="bi bi-kanban"></i>
                        </div>
                        <h6 class="text-uppercase text-secondary m-0"><?= Html::encode($meta['label']) ?></h6>
                    </div>
                    <span class="badge <?= $meta['badge'] ?> bg-opacity-10 text-white rounded-pill px-2 py-1">
                        <?= count($tickets) ?>
                    </span>
                </div>
                <div class="card-body p-2">
                    <div class="kanban-column-body d-flex flex-column gap-2" id="<?= $meta['id'] ?>">
                        <?php foreach ($tickets as $ticket): ?>
                            <?php
                            $priorityColor = 'text-muted';
                            $urgency = $ticket->data_json['urgency'] ?? null;
                            if ($urgency === 'high' || $urgency === '3') {
                                $priorityColor = 'text-danger';
                            } elseif ($urgency === 'medium' || $urgency === '2') {
                                $priorityColor = 'text-warning';
                            } elseif ($urgency === 'low' || $urgency === '1') {
                                $priorityColor = 'text-secondary';
                            }
                            $viewUrl = Url::to(['/helpdesk/service/view-v2', 'id' => $ticket->id]);
                            ?>
                            <div class="card border-0 shadow-sm p-2" data-ticket-id="<?= $ticket->id ?>">
                                <a href="<?= $viewUrl ?>" class="text-decoration-none text-reset">
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-semibold text-primary">#<?= Html::encode($ticket->repair_number) ?></span>
                                            <span class="small <?= $priorityColor ?>">
                                                <?= Html::encode($ticket->UrgencyName() ?? 'ไม่ระบุ') ?>
                                            </span>
                                        </div>
                                        <div class="small fw-medium">
                                            <?= Html::encode($ticket->title) ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center small text-muted">
                                            <span><?= Html::encode($ticket->getUserReq()['fullname']) ?></span>
                                            <span><?= Html::encode($ticket->viewCreated()['date']) ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


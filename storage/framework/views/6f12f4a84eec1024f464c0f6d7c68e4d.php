<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Reminder')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <h1><?php echo e(__('Dashboard')); ?></h1>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Reminder')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><?php echo e(__('Tableau de Bord des Rappels')); ?></h2>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="updateReminderStatuses()">
                            <i class="fas fa-sync"></i> <?php echo e(__('Actualiser')); ?>

                        </button>
                        <a href="<?php echo e(route('reminder.create')); ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> <?php echo e(__('Nouveau Rappel')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                        <h3 class="text-danger mb-1" id="overdue-count">0</h3>
                        <p class="card-text"><?php echo e(__('En Retard')); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                        <h3 class="text-warning mb-1" id="urgent-count">0</h3>
                        <p class="card-text"><?php echo e(__('Urgent')); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar text-info fa-2x mb-2"></i>
                        <h3 class="text-info mb-1" id="upcoming-count">0</h3>
                        <p class="card-text"><?php echo e(__('À Venir')); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-pause text-success fa-2x mb-2"></i>
                        <h3 class="text-success mb-1" id="pending-count">0</h3>
                        <p class="card-text"><?php echo e(__('En Attente')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Urgent Reminders Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bell text-danger"></i>
                            <?php echo e(__('Rappels Urgents et En Retard')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="urgent-reminders-table">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Statut')); ?></th>
                                        <th><?php echo e(__('Nom du Rappel')); ?></th>
                                        <th><?php echo e(__('Véhicule')); ?></th>
                                        <th><?php echo e(__('Type')); ?></th>
                                        <th><?php echo e(__('Date d\'Échéance')); ?></th>
                                        <th><?php echo e(__('Jours Restants')); ?></th>
                                        <th><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="urgent-reminders-body">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Reminders Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i>
                            <?php echo e(__('Tous les Rappels')); ?>

                        </h5>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="col-12">
                                <label for="statusFilter" class="form-label"><?php echo e(__('Filtrer par Statut')); ?></label>
                                <select id="statusFilter" class="form-control form-control-sm mb-10">
                                    <option value=""><?php echo e(__('Tous')); ?></option>
                                    <option value="En Retard"><?php echo e(__('En Retard')); ?></option>
                                    <option value="Urgent"><?php echo e(__('Urgent')); ?></option>
                                    <option value="À Venir"><?php echo e(__('À Venir')); ?></option>
                                    <option value="Terminé"><?php echo e(__('Terminé')); ?></option>
                                    <option value="En Attente"><?php echo e(__('En Attente')); ?></option>
                                </select>
                            </div>
                            <table class="table table-striped" id="all-reminders-table">
                                <thead>
                                    <tr>
                                        <th hidden><?php echo e(__('ID')); ?></th>
                                        <th><?php echo e(__('Statut')); ?></th>
                                        <th><?php echo e(__('Nom')); ?></th>
                                        <th><?php echo e(__('Véhicule')); ?></th>
                                        <th><?php echo e(__('Type')); ?></th>
                                        <th><?php echo e(__('Date d\'Échéance')); ?></th>
                                        <th><?php echo e(__('Notes')); ?></th>
                                        <th><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $reminders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reminder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="reminder-row" data-status="<?php echo e($reminder->status); ?>">
                                            <td hidden><?php echo e($reminder->id); ?></td>
                                            <td>
                                                <?php switch($reminder->status):
                                                    case ('overdue'): ?>
                                                        <span class="badge badge-danger"><?php echo e(__('En Retard')); ?></span>
                                                    <?php break; ?>

                                                    <?php case ('urgent'): ?>
                                                        <span class="badge badge-warning"><?php echo e(__('Urgent')); ?></span>
                                                    <?php break; ?>

                                                    <?php case ('upcoming'): ?>
                                                        <span class="badge badge-info"><?php echo e(__('À Venir')); ?></span>
                                                    <?php break; ?>

                                                    <?php case ('completed'): ?>
                                                        <span class="badge badge-success"><?php echo e(__('Terminé')); ?></span>
                                                    <?php break; ?>

                                                    <?php default: ?>
                                                        <span class="badge badge-success"><?php echo e(__('En Attente')); ?></span>
                                                <?php endswitch; ?>
                                            </td>
                                            <td><?php echo e($reminder->name); ?></td>
                                            <td>
                                                <?php if($reminder->vehicles): ?>
                                                    <?php echo e($reminder->vehicles->model); ?> -
                                                    <?php echo e($reminder->vehicles->license_plate); ?>

                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo e(__('N/A')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($reminder->reminderType): ?>
                                                    <?php echo e($reminder->reminderType->type); ?>

                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo e(__('N/A')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo e(\Carbon\Carbon::parse($reminder->reminder_date)->format('d/m/Y')); ?>

                                                <br>
                                                <small class="text-muted">
                                                    <?php
                                                        $daysRemaining = \Carbon\Carbon::now()->diffInDays(
                                                            \Carbon\Carbon::parse($reminder->reminder_date),
                                                            false,
                                                        );
                                                    ?>
                                                    <?php if($daysRemaining > 0): ?>
                                                        <?php echo e($daysRemaining); ?> <?php echo e(__('jours restants')); ?>

                                                    <?php elseif($daysRemaining < 0): ?>
                                                        <?php echo e(abs($daysRemaining)); ?> <?php echo e(__('jours de retard')); ?>

                                                    <?php else: ?>
                                                        <?php echo e(__('Aujourd\'hui')); ?>

                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="text-truncate" style="max-width: 150px; display: inline-block;"
                                                    title="<?php echo e($reminder->note); ?>">
                                                    <?php echo e($reminder->note ?: __('Aucune note')); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit reminder')): ?>
                                                        <a href="<?php echo e(route('reminder.edit', $reminder->id)); ?>"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="<?php echo e(__('Modifier')); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if($reminder->status !== 'completed'): ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit reminder')): ?>
                                                            <button class="btn btn-sm btn-outline-success"
                                                                onclick="markAsCompleted(<?php echo e($reminder->id); ?>)"
                                                                title="<?php echo e(__('Marquer comme terminé')); ?>">
                                                                <i class="fa fa-check-square-o"></i>
                                                            </button>
                                                            
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete reminder')): ?>
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteReminder(<?php echo e($reminder->id); ?>)"
                                                            title="<?php echo e(__('Supprimer')); ?>">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Snooze Modal -->
    <div class="modal fade" id="snoozeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo e(__('Reporter le Rappel')); ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="snoozeForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="snoozeDays"><?php echo e(__('Reporter de combien de jours?')); ?></label>
                            <select class="form-control" id="snoozeDays" name="days" required>
                                <option value="1">1 <?php echo e(__('jour')); ?></option>
                                <option value="3">3 <?php echo e(__('jours')); ?></option>
                                <option value="7" selected>7 <?php echo e(__('jours')); ?></option>
                                <option value="14">14 <?php echo e(__('jours')); ?></option>
                                <option value="30">30 <?php echo e(__('jours')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal"><?php echo e(__('Annuler')); ?></button>
                        <button type="submit" class="btn btn-warning"><?php echo e(__('Reporter')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            // Load dashboard data
            loadDashboardData();

            // Refresh data every 5 minutes
            setInterval(loadDashboardData, 300000);

            // Initialize DataTables
            // $('#all-reminders-table').DataTable({
            //     "order": [
            //         [0, "desc"]
            //     ], // Sort by date
            //     "language": {
            //         "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
            //     }
            // });

            let allRemindersTable = $('#all-reminders-table').DataTable({
                order: [
                    [0, 'desc']
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
                }
            });

            // Filter by status
            $('#statusFilter').on('change', function() {
                let selectedStatus = $(this).val();
                allRemindersTable.column(1).search(selectedStatus).draw();
            });

        });

        function loadDashboardData() {
            $.ajax({
                url: '<?php echo e(route('reminder.dashboard.data')); ?>',
                type: 'GET',
                success: function(data) {
                    // Update statistics
                    $('#overdue-count').text(data.stats.overdue);
                    $('#urgent-count').text(data.stats.urgent);
                    $('#upcoming-count').text(data.stats.upcoming);
                    $('#pending-count').text(data.stats.pending);

                    // Update urgent reminders table
                    updateUrgentRemindersTable(data.upcoming);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading dashboard data:', error);
                    showNotification('Erreur lors du chargement des données', 'error');
                }
            });
        }

        function updateUrgentRemindersTable(reminders) {
            const tbody = $('#urgent-reminders-body');
            tbody.empty();

            if (reminders.length === 0) {
                tbody.append(
                    '<tr><td colspan="7" class="text-center text-muted"><?php echo e(__('Aucun rappel urgent')); ?></td></tr>');
                return;
            }

            reminders.forEach(function(reminder) {
                const daysRemaining = calculateDaysRemaining(reminder.reminder_date);
                const statusBadge = getStatusBadge(reminder.status);
                const vehicleName = reminder.vehicles ? reminder.vehicles.model : 'N/A';
                const reminderType = reminder.reminder_type ? reminder.reminder_type.type : 'N/A';
                const vehicleLicensePlate = reminder.vehicles && reminder.vehicles.license_plate ? reminder.vehicles
                    .license_plate : 'N/A';
                const row = `
            <tr class="reminder-row-${reminder.status}">
                <td>${statusBadge}</td>
                <td><strong>${reminder.name}</strong></td>
                <td>${vehicleName} - ${vehicleLicensePlate}</td>
                <td>${reminderType}</td>
                <td>${formatDate(reminder.reminder_date)}</td>
                <td>${daysRemaining}</td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="/reminder/${reminder.id}/edit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-success" onclick="markAsCompleted(${reminder.id})">
                            <i class="fa fa-check-square-o"></i>
                        </button>
                        
                    </div>
                </td>
            </tr>
        `;
                tbody.append(row);
            });
        }

        function getStatusBadge(status) {
            switch (status) {
                case 'overdue':
                    return '<span class="badge badge-danger"><?php echo e(__('En Retard')); ?></span>';
                case 'urgent':
                    return '<span class="badge badge-warning"><?php echo e(__('Urgent')); ?></span>';
                case 'upcoming':
                    return '<span class="badge badge-info"><?php echo e(__('À Venir')); ?></span>';
                case 'completed':
                    return '<span class="badge badge-success"><?php echo e(__('Terminé')); ?></span>';
                default:
                    return '<span class="badge badge-secondary"><?php echo e(__('En Attente')); ?></span>';
            }
        }

        function calculateDaysRemaining(dateString) {
            const today = new Date();
            const reminderDate = new Date(dateString);
            const timeDiff = reminderDate.getTime() - today.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

            if (daysDiff > 0) {
                return `${daysDiff} <?php echo e(__('jours restants')); ?>`;
            } else if (daysDiff < 0) {
                return `${Math.abs(daysDiff)} <?php echo e(__('jours de retard')); ?>`;
            } else {
                return '<?php echo e(__("Aujourd\'hui")); ?>';
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        }

        function updateReminderStatuses() {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo e(__('Mise à jour...')); ?>';
            button.disabled = true;

            $.ajax({
                url: '<?php echo e(route('reminder.update.statuses')); ?>',
                type: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>'
                },
                success: function(data) {
                    if (data.success) {
                        showNotification(
                            `<?php echo e(__('Statuts mis à jour avec succès')); ?>. ${data.updated} <?php echo e(__('rappels modifiés')); ?>.`,
                            'success');
                        loadDashboardData();
                        // Reload page to update the table
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showNotification('<?php echo e(__('Erreur lors de la mise à jour')); ?>: ' + data.error, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('<?php echo e(__('Erreur lors de la mise à jour')); ?>: ' + error, 'error');
                },
                complete: function() {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            });
        }

        function markAsCompleted(reminderId) {
            if (confirm('<?php echo e(__('Êtes-vous sûr de vouloir marquer ce rappel comme terminé?')); ?>')) {
                $.ajax({
                    url: `/reminder/${reminderId}/complete`,
                    type: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(data) {
                        showNotification('<?php echo e(__('Rappel marqué comme terminé')); ?>', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        showNotification('<?php echo e(__('Erreur lors de la mise à jour')); ?>: ' + error, 'error');
                    }
                });
            }
        }

        function snoozeReminder(reminderId) {
            $('#snoozeForm').attr('action', `/reminder/${reminderId}/snooze`);
            $('#snoozeModal').modal('show');
        }

        function deleteReminder(reminderId) {
            if (confirm('<?php echo e(__('Êtes-vous sûr de vouloir supprimer ce rappel?')); ?>')) {
                $.ajax({
                    url: `/reminder/${reminderId}`,
                    type: 'DELETE',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(data) {
                        showNotification('<?php echo e(__('Rappel supprimé avec succès')); ?>', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr, status, error) {
                        // showNotification('<?php echo e(__('Erreur lors de la suppression')); ?>: ' + error, 'error');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                });
            }
        }

        function showNotification(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

            const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);

            $('body').append(notification);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                notification.alert('close');
            }, 5000);
        }

        // Handle snooze form submission
        $('#snoozeForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(data) {
                    $('#snoozeModal').modal('hide');
                    showNotification('<?php echo e(__('Rappel reporté avec succès')); ?>', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    showNotification('<?php echo e(__('Erreur lors du report')); ?>: ' + error, 'error');
                }
            });
        });
    </script>

    <style>
        .reminder-row-overdue {
            background-color: #f8d7da !important;
        }

        .reminder-row-urgent {
            background-color: #fff3cd !important;
        }

        .reminder-row-upcoming {
            background-color: #d1ecf1 !important;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .badge {
            font-size: 0.75em;
        }

        .notification-alert {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .btn-group .btn {
            margin-right: 2px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/car_directonderweg/resources/views/reminder/index.blade.php ENDPATH**/ ?>
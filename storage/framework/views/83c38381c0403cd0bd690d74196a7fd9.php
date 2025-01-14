<!-- resources/views/partials/_date_modal.blade.php -->

<div class="modal-body">
    <div class="row">
        <div id="daysRemaining"></div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all reminder date links
    const reminderLinks = document.querySelectorAll('.reminder-date');
    // Initialize the modal
    const dateModal = new bootstrap.Modal(document.getElementById('dateModal'));
    
    reminderLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const reminderDate = this.getAttribute('data-date');
            const today = new Date();
            const reminder = new Date(reminderDate);
            const diffTime = reminder.getTime() - today.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            let message = '';
            if (diffDays > 0) {
                message = `<?php echo e(__('There are')); ?> ${diffDays} <?php echo e(__('days remaining until this reminder.')); ?>`;
            } else if (diffDays < 0) {
                message = `<?php echo e(__('This reminder is overdue by')); ?> ${Math.abs(diffDays)} <?php echo e(__('days.')); ?>`;
            } else {
                message = `<?php echo e(__('This reminder is due today!')); ?>`;
            }
            
            document.getElementById('daysRemaining').textContent = message;
            dateModal.show();
        });
    });
});
</script><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/reminder/_date_modal.blade.php ENDPATH**/ ?>
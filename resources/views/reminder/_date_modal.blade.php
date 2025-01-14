<!-- resources/views/partials/_date_modal.blade.php -->
{{-- <div class="modal fade" id="dateModal" tabindex="-1" aria-labelledby="dateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateModalLabel">{{__('Reminder Date Details')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="daysRemaining"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
            </div>
        </div>
    </div>
</div> --}}
<div class="modal-body">
    <div class="row">
        <div id="daysRemaining"></div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{__('Close')}}</button>
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
                message = `{{__('There are')}} ${diffDays} {{__('days remaining until this reminder.')}}`;
            } else if (diffDays < 0) {
                message = `{{__('This reminder is overdue by')}} ${Math.abs(diffDays)} {{__('days.')}}`;
            } else {
                message = `{{__('This reminder is due today!')}}`;
            }
            
            document.getElementById('daysRemaining').textContent = message;
            dateModal.show();
        });
    });
});
</script>
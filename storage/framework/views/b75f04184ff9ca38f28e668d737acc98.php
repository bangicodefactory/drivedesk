<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Create Signature</h4>
        </div>
        
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('signature.store')); ?>" id="signatureForm">
                <?php echo csrf_field(); ?>
                
                <div class="mb-3">
                    <label class="form-label">Select Client</label>
                    <select name="user_id" class="form-select basic-select" required>
                        <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Signature:</label>
                    <div class="border rounded p-3 bg-white">
                        <canvas id="signatureCanvas" style="border: 1px solid #dee2e6; width: 100%; "></canvas>
                    </div>
                    <input type="hidden" name="signature" id="signatureData">
                    <?php $__errorArgs = ['signature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-danger" id="clearButton">
                        Clear Signature
                    </button>
                    <button type="submit" class="btn btn-success">
                        Save Signature
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signatureCanvas');
        const context = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            context.fillStyle = '#fff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.strokeStyle = '#000';
            context.lineWidth = 2;
            context.lineCap = 'round';
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getCoords(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches && e.touches.length > 0) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top
                };
            } else {
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };
            }
        }

        function startDrawing(e) {
            isDrawing = true;
            const pos = getCoords(e);
            lastX = pos.x;
            lastY = pos.y;
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault(); // Prevent scrolling on touch
            const pos = getCoords(e);
            context.beginPath();
            context.moveTo(lastX, lastY);
            context.lineTo(pos.x, pos.y);
            context.stroke();
            lastX = pos.x;
            lastY = pos.y;
            document.getElementById('signatureData').value = canvas.toDataURL();
        }

        function stopDrawing() {
            isDrawing = false;
        }

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch events
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
        canvas.addEventListener('touchcancel', stopDrawing);

        document.getElementById('clearButton').addEventListener('click', function () {
            context.fillStyle = '#fff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signatureData').value = '';
        });

        document.getElementById('signatureForm').addEventListener('submit', function(e) {
            if (!document.getElementById('signatureData').value) {
                e.preventDefault();
                alert('Please provide a signature before submitting');
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/signature/create.blade.php ENDPATH**/ ?>
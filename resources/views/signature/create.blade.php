@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Create Signature</h4>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('signature.store') }}" id="signatureForm">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Select Client</label>
                    <select name="user_id" class="form-select basic-select" required>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Signature:</label>
                    <div class="border rounded p-3 bg-white">
                        <canvas id="signatureCanvas" style="border: 1px solid #dee2e6; width: 100%; height: 200px; touch-action: none;"></canvas>
                    </div>
                    <input type="hidden" name="signature" id="signatureData">
                    @error('signature')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
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
    // Wait for the DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signatureCanvas');
        const context = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Set canvas size
        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            // Set white background
            context.fillStyle = '#fff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            // Set drawing style
            context.strokeStyle = '#000';
            context.lineWidth = 2;
            context.lineCap = 'round';
        }

        // Call resize initially and on window resize
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Get coordinates function that works for both mouse and touch
        function getCoordinates(e) {
            const rect = canvas.getBoundingClientRect();
            let clientX, clientY;
            
            if (e.touches && e.touches.length > 0) {
                // Touch event
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else {
                // Mouse event
                clientX = e.clientX;
                clientY = e.clientY;
            }
            
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        // Drawing functions
        function startDrawing(e) {
            e.preventDefault(); // Prevent scrolling on touch
            isDrawing = true;
            const coords = getCoordinates(e);
            lastX = coords.x;
            lastY = coords.y;
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault(); // Prevent scrolling on touch
            
            const coords = getCoordinates(e);
            
            context.beginPath();
            context.moveTo(lastX, lastY);
            context.lineTo(coords.x, coords.y);
            context.stroke();

            lastX = coords.x;
            lastY = coords.y;
            
            // Update hidden input with signature data
            document.getElementById('signatureData').value = canvas.toDataURL();
        }

        function stopDrawing(e) {
            if (isDrawing) {
                e.preventDefault();
                isDrawing = false;
            }
        }

        // Mouse event listeners
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch event listeners
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
        canvas.addEventListener('touchcancel', stopDrawing);

        // Clear button functionality
        document.getElementById('clearButton').addEventListener('click', function() {
            context.fillStyle = '#fff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signatureData').value = '';
        });

        // Form submission validation
        document.getElementById('signatureForm').addEventListener('submit', function(e) {
            if (!document.getElementById('signatureData').value) {
                e.preventDefault();
                alert('Please provide a signature before submitting');
            }
        });
    });
</script>
@endsection
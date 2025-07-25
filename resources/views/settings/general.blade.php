@extends('layouts.app')
@section('page-title')
    {{__('General Settings')}}
@endsection
@php
    $settings=settings();

@endphp
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}"><h1>{{__('Dashboard')}}</h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{__('General Settings')}}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    {{Form::model($settings, array('route' => array('setting.general'), 'method' => 'post', 'enctype' => "multipart/form-data")) }}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('application_name',__('Application Name'),array('class'=>'form-label'))}}
                                {{Form::text('application_name',!empty($settings['app_name'])?$settings['app_name']:env('APP_NAME'),array('class'=>'form-control','placeholder'=>__('Enter your application name'),'required'=>'required'))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{Form::label('logo',__('Logo'),array('class'=>'form-label'))}}
                                {{Form::file('logo',array('class'=>'form-control'))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{Form::label('favicon',__('Favicon'),array('class'=>'form-label'))}}
                                {{Form::file('favicon',array('class'=>'form-control'))}}
                            </div>
                        </div>
                        @if(\Auth::user()->type=='super admin')
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('landing_logo',__('Landing Page Logo'),array('class'=>'form-label'))}}
                                    {{Form::file('landing_logo',array('class'=>'form-control'))}}
                                </div>
                            </div>

                        @endif
                    </div>
                    <div class="col-md-12">
    <div class="form-group">
        {{ Form::label('signature', __('Company Signature'), ['class' => 'form-label']) }}

        <div class="border rounded p-3 bg-white">
            <canvas id="signatureCanvas" style="border: 1px solid #dee2e6; width: 100%; height: 200px;"></canvas>
        </div>
        <input type="hidden" name="company_signature" id="signatureData">

        @if(!empty($settings['company_signature']) && file_exists(storage_path('app/public/upload/signature-company/'.$settings['company_signature'])))
            <div class="mt-3">
                <strong>{{ __('Current Signature') }}:</strong><br>
                <img src="{{ asset('storage/upload/signature-company/' . $settings['company_signature']) }}" height="100">
            </div>
        @endif

        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-danger" id="clearButton">{{ __('Clear Signature') }}</button>
        </div>
    </div>
</div>

                    <div class="text-right">
                        {{Form::submit(__('Save'),array('class'=>'btn btn-primary btn-rounded'))}}
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('signatureCanvas');
            const context = canvas.getContext('2d');
            let isDrawing = false;
            let lastX = 0;
            let lastY = 0;
            
            function resizeCanvas() {
                const rect = canvas.parentElement.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
                
                context.strokeStyle = '#000000ff';
                context.lineWidth = 2;
                context.lineCap = 'round';
                context.lineJoin = 'round';
                
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
            }
            
            resizeCanvas();
            
            window.addEventListener('resize', resizeCanvas);
            
            function getCoords(e) {
                const rect = canvas.getBoundingClientRect();
                let clientX, clientY;
                
                if (e.type.includes('touch')) {
                    clientX = e.touches[0].clientX;
                    clientY = e.touches[0].clientY;
                } else {
                    clientX = e.clientX;
                    clientY = e.clientY;
                }
                
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }
            
            function startDrawing(e) {
                isDrawing = true;
                const coords = getCoords(e);
                [lastX, lastY] = [coords.x, coords.y];
            }
            
            function draw(e) {
                if (!isDrawing) return;
                
                const coords = getCoords(e);
                context.beginPath();
                context.moveTo(lastX, lastY);
                context.lineTo(coords.x, coords.y);
                context.stroke();
                [lastX, lastY] = [coords.x, coords.y];
            }
            
            function stopDrawing() {
                isDrawing = false;
                updatePreview();
            }
            
            function updatePreview() {
                const dataUrl = canvas.toDataURL();
                document.getElementById('signaturePreview').src = dataUrl;
                document.getElementById('signatureDataUrl').textContent = dataUrl.substring(0, 100) + '...';
            }
            
            function clearCanvas() {
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                document.getElementById('signaturePreview').src = '';
                document.getElementById('signatureDataUrl').textContent = '';
            }
            
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            
            canvas.addEventListener('touchstart', startDrawing);
            canvas.addEventListener('touchmove', function(e) {
                e.preventDefault();
                draw(e);
            }, { passive: false });
            canvas.addEventListener('touchend', stopDrawing);
            canvas.addEventListener('touchcancel', stopDrawing);
            
            document.getElementById('clearButton').addEventListener('click', clearCanvas);
            
            document.getElementById('saveButton').addEventListener('click', function() {
                if (document.getElementById('signaturePreview').src) {
                    alert('Signature saved successfully!');
                } else {
                    alert('Please create a signature first.');
                }
            });
            
            context.font = '20px Arial';
            context.fillStyle = '#e0e0e0';
            context.textAlign = 'center';
            context.fillText('Draw your signature above', canvas.width / 2, canvas.height / 2);
        });
    </script>

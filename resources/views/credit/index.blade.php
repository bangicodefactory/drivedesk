@extends('layouts.app')
@section('page-title')
    {{ __('credit.title') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <h1>{{ __('credit.dashboard') }}</h1>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ __('credit.title') }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if (Gate::check('manage driver'))
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="md"
           data-url="{{ route('credit.create') }}"
           data-title="{{ __('credit.add_credit') }}"> <i class="ti-plus mr-5"></i>
            {{ __('credit.add_credit') }}
        </a>
    @endif
@endsection
@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <form method="GET" action="{{ route('credit.index') }}" id="credit-filter-form">
                <div class="d-flex flex-wrap gap-3 align-items-end">
                    <div>
                        <label for="driver_id" class="form-label">{{ __('credit.driver') }}</label>
                        <select id="driver_id" name="driver_id" class="form-control basic-select">
                            <option value="">{{ __('credit.all_drivers') }}</option>
                            @foreach ($drivers as $d)
                                <option value="{{ $d->id }}" {{ request()->get('driver_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="driver_name" class="form-label">{{ __('credit.search_by_driver_name') }}</label>
                        <input type="text" id="driver_name" name="driver_name" class="form-control"
                               placeholder="{{ __('credit.driver_name') }}"
                               value="{{ request()->get('driver_name') }}">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">{{ __('credit.filter') }}</button>
                        <a href="{{ route('credit.index') }}" class="btn btn-secondary">{{ __('credit.reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="display dataTable cell-border datatbl-advance" id="creditsTable">
                        <thead>
                            <tr>
                                <th>{{ __('credit.driver') }}</th>
                                <th>{{ __('credit.amount') }}</th>
                                <th>{{ __('credit.status') }}</th>
                                <th>{{ __('credit.date_credit') }}</th>
                                <th>{{ __('credit.creation_date') }}</th>
                                @if (Gate::check('manage driver'))
                                    <th>{{ __('credit.action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($credits as $credit)
                                <tr>
                                    <td>{{ $credit->driver ? $credit->driver->name : __('credit.n_a') }}</td>
                                    <td>{{ number_format($credit->amount, 2) }} {{ __('credit.currency') }}</td>
                                    <td>
                                        @if ($credit->status === 'payé')
                                            <span class="badge badge-success">{{ __('credit.status_paid') }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ __('credit.status_unpaid') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $credit->credit_date ? dateFormat($credit->credit_date) : __('credit.none') }}</td>
                                    <td>{{ dateFormat($credit->created_at) }}</td>
                                    @if (Gate::check('manage driver'))
                                        <td>
                                            <div class="cart-action">
                                                <a class="text-success customModal" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{ __('credit.edit') }}" href="#"
                                                   data-url="{{ route('credit.edit', $credit) }}"
                                                   data-title="{{ __('credit.edit_credit') }}" data-size="md">
                                                    <i data-feather="edit"></i>
                                                </a>
                                                <a href="#" class="text-danger credit-delete-btn" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{ __('credit.delete') }}"
                                                   data-url="{{ route('credit.destroy', $credit) }}">
                                                    <i data-feather="trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#creditsTable')) {
                $('#creditsTable').DataTable().destroy();
            }
            $('#creditsTable').DataTable({
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('credit.all') }}"]],
                searching: true,
                ordering: true,
                order: [[4, 'desc']],
                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json" },
                columnDefs: [
                    @if (Gate::check('manage driver'))
                    { targets: 5, orderable: false }
                    @endif
                ]
            });
            $('#driver_id').on('change', function() {
                $('#credit-filter-form').submit();
            });

            // Delete credit with confirmation modal
            $(document).on('click', '.credit-delete-btn', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                Swal.fire({
                    title: "{{ __('credit.confirm_deletion') }}",
                    text: "{{ __('credit.delete_credit_confirm') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{ __('credit.yes_delete_it') }}"
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var form = $('<form>', {
                            method: 'POST',
                            action: url
                        }).append(
                            $('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }),
                            $('<input>', { type: 'hidden', name: '_token', value: $('meta[name="csrf-token"]').attr('content') })
                        );
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush

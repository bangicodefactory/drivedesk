@extends('layouts.app')

@section('page-title')
    {{ __('Renumber TVA Invoices') }}
@endsection

@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <h1>{{ __('Dashboard') }}</h1>
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('tva.index') }}">{{ __('TVA') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Renumber') }}</a>
        </li>
    </ul>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- ── Left column : controls ─────────────────────────────────── --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center">
                    <i class="ti ti-refresh me-2"></i>
                    <h5 class="mb-0">{{ __('Renumber Invoices') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Resequence invoice numbers (facture_number) for the selected year, ordered by invoice date then id. Soft-deleted invoices are skipped.') }}
                    </p>

                    <div class="mb-3">
                        <label for="year-select" class="form-label">
                            <i class="ti ti-calendar me-1"></i> {{ __('Year') }}
                        </label>
                        <select id="year-select" class="form-select">
                            @if ($years->isEmpty())
                                <option value="{{ $selectedYear }}" selected>{{ $selectedYear }}</option>
                            @else
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" {{ (int) $y === (int) $selectedYear ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                                @if (! $years->contains((int) $selectedYear))
                                    <option value="{{ $selectedYear }}" selected>{{ $selectedYear }}</option>
                                @endif
                            @endif
                        </select>
                    </div>

                    <div class="mb-3 d-flex align-items-center">
                        <span class="me-2">
                            <i class="ti ti-hash me-1"></i> {{ __('Invoices') }}
                        </span>
                        <span id="count-badge" class="badge bg-primary fs-6">{{ $preview['count'] }}</span>
                    </div>

                    <button type="button"
                            id="apply-btn"
                            class="btn btn-success w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmModal"
                            {{ $preview['count'] === 0 ? 'disabled' : '' }}>
                        <i class="ti ti-refresh me-1"></i> {{ __('Apply Renumbering') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Right column : preview table ───────────────────────────── --}}
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-list-numbers me-2"></i>
                        <h5 class="mb-0">{{ __('Preview') }} — <span id="preview-year">{{ $selectedYear }}</span></h5>
                    </div>
                    <small class="text-muted">{{ __('Ordered by invoice date, then id') }}</small>
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 70vh; overflow:auto;">
                        <table id="preview-table" class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width:60px;">#</th>
                                    <th>
                                        <i class="ti ti-calendar me-1"></i> {{ __('Invoice Date') }}
                                    </th>
                                    <th>{{ __('Current Number') }}</th>
                                    <th>{{ __('New Number') }}</th>
                                </tr>
                            </thead>
                            <tbody id="preview-tbody">
                                @forelse ($preview['records'] as $idx => $rec)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $rec['date'] ?? '—' }}</td>
                                        <td><span class="badge bg-secondary">{{ $rec['old_number'] ?? '—' }}</span></td>
                                        <td><span class="badge bg-success">{{ $rec['new_number'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr id="preview-empty-row">
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="ti ti-database-off me-1"></i>
                                            {{ __('No invoices found for this year.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Confirm modal ──────────────────────────────────────────────── --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">
                        <i class="ti ti-refresh me-1"></i> {{ __('Confirm Renumbering') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            id="confirm-close-btn"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        {{ __('You are about to renumber') }}
                        <strong id="confirm-count">{{ $preview['count'] }}</strong>
                        {{ __('invoice(s) for the year') }}
                        <strong id="confirm-year">{{ $selectedYear }}</strong>.
                    </p>
                    <div class="alert alert-warning mb-0">
                        <i class="ti ti-alert-triangle me-1"></i>
                        {{ __('This action will overwrite existing facture numbers and cannot be undone.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="confirm-cancel-btn">
                        {{ __('Cancel') }}
                    </button>
                    <form method="POST" action="{{ route('tva.renumber.apply') }}" id="apply-form" class="d-inline m-0">
                        @csrf
                        <input type="hidden" name="year" id="confirm-year-input" value="{{ $selectedYear }}">
                        <button type="submit" class="btn btn-success" id="confirm-submit-btn">
                            <i class="ti ti-check me-1"></i> {{ __('Confirm & Apply') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const previewUrl = @json(route('tva.renumber.preview'));

        // ── Teleport confirm modal out of the simplebar wrapper so clicks
        //    register reliably (the theme's .theme-body[data-simplebar] wrapper
        //    can otherwise intercept / offset pointer events).
        const modalEl = document.getElementById('confirmModal');
        if (modalEl && modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }

        const yearSelect = document.getElementById('year-select');
        const tbody = document.getElementById('preview-tbody');
        const countBadge = document.getElementById('count-badge');
        const applyBtn = document.getElementById('apply-btn');
        const previewYear = document.getElementById('preview-year');
        const confirmYearInput = document.getElementById('confirm-year-input');
        const confirmYear = document.getElementById('confirm-year');
        const confirmCount = document.getElementById('confirm-count');
        const cancelBtn = document.getElementById('confirm-cancel-btn');
        const closeBtn  = document.getElementById('confirm-close-btn');
        const submitBtn = document.getElementById('confirm-submit-btn');
        const applyForm = document.getElementById('apply-form');

        // ── Fallback handlers in case data-bs-dismiss is intercepted by the
        //    theme or Bootstrap's JS isn't bound to this dynamically moved node.
        function getOrCreateModal() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getOrCreateInstance(modalEl);
        }

        function closeModal() {
            const inst = getOrCreateModal();
            if (inst) {
                inst.hide();
                return;
            }
            // Hard-fallback: manually hide if Bootstrap JS missing.
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        }

        if (cancelBtn) cancelBtn.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
        if (closeBtn)  closeBtn.addEventListener('click',  function (e) { e.preventDefault(); closeModal(); });

        // Ensure the submit button actually submits even if some global
        // handler calls preventDefault on <button type="submit"> clicks.
        if (submitBtn && applyForm) {
            submitBtn.addEventListener('click', function (e) {
                if (submitBtn.disabled) return;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' +
                    @json(__('Applying…'));
                // Give the browser a tick then submit programmatically.
                setTimeout(() => applyForm.submit(), 0);
            });
        }

        const txtNoInvoices = @json(__('No invoices found for this year.'));
        const txtLoading    = @json(__('Loading…'));
        const txtError      = @json(__('Failed to load preview.'));

        function escapeHtml(s) {
            if (s === null || s === undefined) return '—';
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderRows(records) {
            if (!records || records.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="4" class="text-center text-muted py-4">' +
                    '<i class="ti ti-database-off me-1"></i>' + escapeHtml(txtNoInvoices) +
                    '</td></tr>';
                return;
            }

            const html = records.map((rec, idx) => {
                return '<tr>' +
                    '<td>' + (idx + 1) + '</td>' +
                    '<td>' + escapeHtml(rec.date) + '</td>' +
                    '<td><span class="badge bg-secondary">' + escapeHtml(rec.old_number) + '</span></td>' +
                    '<td><span class="badge bg-success">' + escapeHtml(rec.new_number) + '</span></td>' +
                    '</tr>';
            }).join('');

            tbody.innerHTML = html;
        }

        function setLoading() {
            tbody.innerHTML =
                '<tr><td colspan="4" class="text-center text-muted py-4">' +
                '<span class="spinner-border spinner-border-sm me-2"></span>' + escapeHtml(txtLoading) +
                '</td></tr>';
        }

        function setError() {
            tbody.innerHTML =
                '<tr><td colspan="4" class="text-center text-danger py-4">' +
                '<i class="ti ti-alert-triangle me-1"></i>' + escapeHtml(txtError) +
                '</td></tr>';
        }

        function refresh() {
            const year = yearSelect.value;
            if (!year) return;

            previewYear.textContent = year;
            confirmYearInput.value = year;
            confirmYear.textContent = year;

            setLoading();
            applyBtn.disabled = true;

            const url = previewUrl + '?year=' + encodeURIComponent(year);

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then(function (resp) {
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    return resp.json();
                })
                .then(function (data) {
                    const count = data.count || 0;
                    countBadge.textContent = count;
                    confirmCount.textContent = count;
                    applyBtn.disabled = count === 0;
                    renderRows(data.records || []);
                })
                .catch(function () {
                    countBadge.textContent = '0';
                    confirmCount.textContent = '0';
                    applyBtn.disabled = true;
                    setError();
                });
        }

        yearSelect.addEventListener('change', refresh);
    })();
</script>
@endpush

<div class="modal-body">
    <div class="product-card">
        <div class="row">
            <div class="col-6">
                <div class="detail-group">
                    @php
        $factureNumber = $tva->facture_number;
    $prefix = bookingPrefix();
    $displayFactureNumber = Str::startsWith($factureNumber, $prefix) ? $factureNumber : $prefix . $factureNumber;
@endphp
                    <h6>{{ __('Facture Number') }}</h6>
                    <!-- To avoid prefix duplication in case of edits -->
                    <p class="mb-20">{{ $displayFactureNumber }}</p> 
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{ __('Facture Date') }}</h6>
                    <p class="mb-20">{{ \Carbon\Carbon::parse($tva->facture_date)->format('Y-m-d') }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{ __('Client Name') }}</h6>
                    <p class="mb-20">{{ $tva->client_name ?? '-' }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{ __('Duration (Quantity)') }}</h6>
                    <p class="mb-20">{{ $tva->quantity }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{ __('Unit Price HT') }}</h6>
                    <p class="mb-20">{{ number_format($tva->unit_price_ht, 2) }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{ __('Total HT') }}</h6>
                    <p class="mb-20">{{ number_format($tva->total_ht, 2) }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{ __('TVA (Tax)') }}</h6>
                    <p class="mb-20">{{ number_format($tva->tva, 2) }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{ __('Montant TTC') }}</h6>
                    <p class="mb-20">{{ number_format($tva->montant_ttc, 2) }}</p>
                </div>
            </div>
            <div class="col-12">
                <div class="detail-group">
                    <h6>{{ __('Vehicle') }}</h6>
                    <p class="mb-20">{{ $tva->designation ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<x-filament-panels::page>
    @php
        $overview = $this->getOverviewData()['summary'];
        $moneyBalanceLabel = $overview['amount_balance'];
        $samplesBalanceLabel = $overview['samples_balance'];
    @endphp

    <div class="contract-overview-report">
        <div class="overview-toolbar no-print">
            <p class="overview-period-note">
                {{ $this->getPeriodLabel() }}. Ο προϋπολογισμός αφορά όλη τη διάρκεια της σύμβασης.
            </p>

            <x-filament::button
                tag="a"
                :href="route('admin.contracts.overview.pdf', array_filter(['contract' => $this->record->getKey(), 'from' => $this->from, 'to' => $this->to]))"
                target="_blank"
                rel="noopener"
                icon="heroicon-o-printer"
                color="gray"
            >
                Προβολή / εκτύπωση PDF
            </x-filament::button>
        </div>

        <div class="no-print contract-overview-filters">
            <div class="overview-filter-panel" aria-label="Φίλτρο περιόδου πρωτοκόλλων">
                <div class="overview-date-field">
                    <label for="contract-overview-from">Πρωτόκολλα από</label>
                    <input id="contract-overview-from" type="date" wire:model.live="from">
                </div>

                <div class="overview-date-field">
                    <label for="contract-overview-to">Πρωτόκολλα έως</label>
                    <input id="contract-overview-to" type="date" wire:model.live="to">
                </div>

                <div class="overview-period-presets" aria-label="Γρήγορη επιλογή περιόδου">
                    <span class="overview-period-presets-label">Γρήγορη περίοδος:</span>
                    <x-filament::button size="sm" color="gray" wire:click="setPeriodPreset('contract_to_date')">
                        Από αρχή σύμβασης
                    </x-filament::button>
                    <x-filament::button size="sm" color="gray" wire:click="setPeriodPreset('current_quarter')">
                        Τρέχον τρίμηνο
                    </x-filament::button>
                    <x-filament::button size="sm" color="gray" wire:click="setPeriodPreset('previous_quarter')">
                        Προηγούμενο τρίμηνο
                    </x-filament::button>
                    <x-filament::button size="sm" color="gray" wire:click="setPeriodPreset('last_four_quarters')">
                        Τελευταία 4 τρίμηνα
                    </x-filament::button>
                    <x-filament::button size="sm" color="gray" wire:click="setPeriodPreset('year_to_date')">
                        Έτος έως σήμερα
                    </x-filament::button>
                </div>
            </div>
        </div>

        <section aria-label="Σύνολα σύμβασης" class="contract-overview-summary-grid">
            <div class="overview-metric">
                <p class="overview-metric-label">Προϋπολογισμός σύμβασης</p>
                <p class="overview-metric-value">{{ number_format($overview['forecasted_amount'], 2, ',', '.') }} €</p>
                <p class="overview-metric-note">Συνολικός, μετά τις ενεργές αναθεωρήσεις</p>
            </div>

            <div class="overview-metric">
                <p class="overview-metric-label">Υλοποιημένο ποσό</p>
                <p class="overview-metric-value">{{ number_format($overview['actual_amount'], 2, ',', '.') }} €</p>
                <p class="overview-metric-note">{{ $this->getPeriodLabel() }}</p>
            </div>

            <div class="overview-metric">
                <p class="overview-metric-label">Οικονομικό υπόλοιπο / υπέρβαση</p>
                <p class="overview-metric-value">{{ $moneyBalanceLabel }}</p>
                <p class="overview-metric-note">Υλοποίηση {{ number_format($overview['progress'], 1, ',', '.') }}%</p>
            </div>

            <div class="overview-metric">
                <p class="overview-metric-label">Προβλεπόμενα δείγματα</p>
                <p class="overview-metric-value">{{ number_format($overview['forecasted_samples'], 0, ',', '.') }}</p>
                <p class="overview-metric-note">Συνολικά για τη σύμβαση</p>
            </div>

            <div class="overview-metric">
                <p class="overview-metric-label">Υλοποιημένα δείγματα</p>
                <p class="overview-metric-value">{{ number_format($overview['actual_samples'], 0, ',', '.') }}</p>
                <p class="overview-metric-note">{{ $this->getPeriodLabel() }}</p>
            </div>

            <div class="overview-metric">
                <p class="overview-metric-label">Υπόλοιπο / υπέρβαση δειγμάτων</p>
                <p class="overview-metric-value">{{ $samplesBalanceLabel }}</p>
                <p class="overview-metric-note">Σε σύγκριση με το σύνολο της σύμβασης</p>
            </div>
        </section>

        {{ $this->table }}
    </div>

    <style>
        .contract-overview-report {
            display: grid;
            gap: 1.25rem;
            max-width: 100%;
        }

        .contract-overview-report .fi-ta-table {
            width: 100%;
            table-layout: fixed;
        }

        .contract-overview-report .fi-ta-cell {
            white-space: normal !important;
            overflow-wrap: anywhere;
        }

        .contract-overview-report .fi-ta-cell:nth-child(1) { width: 24%; }
        .contract-overview-report .fi-ta-cell:nth-child(2) { width: 19%; }
        .contract-overview-report .fi-ta-cell:nth-child(3) { width: 20%; }
        .contract-overview-report .fi-ta-cell:nth-child(4) { width: 23%; }
        .contract-overview-report .fi-ta-cell:nth-child(5) { width: 14%; }

        .overview-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem 1.25rem;
        }

        .overview-period-note {
            color: rgb(75 85 99);
            font-size: 0.875rem;
        }

        .overview-filter-panel {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 0.75rem 1rem;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.75rem;
            background: white;
            padding: 0.875rem 1rem;
        }

        .overview-date-field {
            display: grid;
            gap: 0.35rem;
        }

        .overview-period-presets {
            display: flex;
            flex: 1 1 100%;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }

        .overview-period-presets-label {
            color: rgb(107 114 128);
            font-size: 0.8rem;
        }

        .overview-date-field label {
            color: rgb(75 85 99);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .overview-date-field input {
            min-width: 11rem;
            height: 2.5rem;
            border: 1px solid rgb(209 213 219);
            border-radius: 0.5rem;
            background: white;
            padding: 0 0.65rem;
            color: rgb(17 24 39);
        }

        .overview-date-field input:focus {
            border-color: rgb(37 99 235);
            outline: 2px solid rgb(147 197 253);
            outline-offset: 1px;
        }

        .contract-overview-summary-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
        }

        @media (min-width: 40rem) {
            .contract-overview-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 64rem) {
            .contract-overview-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .overview-metric {
            min-width: 0;
            border: 1px solid rgb(229 231 235);
            border-radius: 0.75rem;
            background: white;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        }

        .overview-metric-label {
            color: rgb(107 114 128);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .overview-metric-value {
            margin-top: 0.35rem;
            color: rgb(17 24 39);
            font-size: clamp(1rem, 1.25vw, 1.25rem);
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .overview-metric-note {
            margin-top: 0.35rem;
            color: rgb(107 114 128);
            font-size: 0.75rem;
        }

        .dark .overview-metric {
            border-color: rgb(55 65 81);
            background: rgb(17 24 39);
        }

        .dark .overview-filter-panel {
            border-color: rgb(55 65 81);
            background: rgb(17 24 39);
        }

        .dark .overview-date-field label {
            color: rgb(209 213 219);
        }

        .dark .overview-period-presets-label {
            color: rgb(156 163 175);
        }

        .dark .overview-date-field input {
            border-color: rgb(75 85 99);
            background: rgb(31 41 55);
            color: rgb(243 244 246);
        }

        .dark .overview-metric-value {
            color: rgb(243 244 246);
        }

    </style>

</x-filament-panels::page>

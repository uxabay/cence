<x-filament-panels::page>
    @php
        $comparisonLabel = $statistics['previous_from']->format('d/m/Y') . ' – ' . $statistics['previous_to']->format('d/m/Y');
        $currentLabel = $statistics['from']->format('d/m/Y') . ' – ' . $statistics['to']->format('d/m/Y');
    @endphp

    <div class="space-y-6">
        @if ($comparePreviousYear && ! $statistics['compare'])
            <x-filament::section icon="heroicon-o-information-circle" icon-color="gray">
                <p class="text-sm">Δεν υπάρχουν ενεργά πρωτόκολλα στην αντίστοιχη περσινή περίοδο με τα επιλεγμένα φίλτρα. Η σύγκριση δεν εμφανίζεται.</p>
            </x-filament::section>
        @endif

        @if ($statistics['undated']['protocols'] > 0)
            <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="warning">
                <p class="text-sm">
                    Εκτός επιλεγμένων περιόδων: <strong>{{ number_format($statistics['undated']['protocols'], 0, ',', '.') }}</strong>
                    ενεργά πρωτόκολλα χωρίς ημερομηνία, με
                    <strong>{{ number_format($statistics['undated']['received'], 0, ',', '.') }}</strong>
                    παραληφθέντα δείγματα. Δεν περιλαμβάνονται στα στατιστικά περιόδου.
                </p>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">Φίλτρα</x-slot>
            <x-slot name="description">Εμφανίζονται ενεργά πρωτόκολλα εντός της επιλεγμένης περιόδου. Πρωτόκολλο θεωρείται συνδεδεμένο με σύμβαση όταν έχει οριστεί σύμβαση ή κατηγορία σύμβασης. Οι κατηγορίες που έχουν σημανθεί για εξαίρεση δεν συμμετέχουν στα εργαστηριακά σύνολα.</x-slot>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                <label class="block min-w-0">
                    <span class="mb-1 block text-sm font-medium">Από</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model.live="from" />
                    </x-filament::input.wrapper>
                </label>

                <label class="block min-w-0">
                    <span class="mb-1 block text-sm font-medium">Έως</span>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model.live="to" />
                    </x-filament::input.wrapper>
                </label>

                <label class="block min-w-0">
                    <span class="mb-1 block text-sm font-medium">Πελάτης</span>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="customerId">
                            <option value="">Όλοι οι πελάτες</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </label>

                <label class="block min-w-0">
                    <span class="mb-1 block text-sm font-medium">Κατηγορία πελάτη</span>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="customerCategoryId">
                            <option value="">Όλες οι κατηγορίες</option>
                            @foreach ($customerCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </label>

                <label class="block min-w-0">
                    <span class="mb-1 block text-sm font-medium">Κατηγορία δείγματος</span>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="sampleCategoryId">
                            <option value="">Όλες οι κατηγορίες</option>
                            @foreach ($sampleCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </label>

                <label class="block min-w-0">
                    <span class="mb-1 block text-sm font-medium">Σύνδεση με σύμβαση</span>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="contractScope">
                            <option value="all">Με και χωρίς σύμβαση</option>
                            <option value="with">Μόνο με σύνδεση σε σύμβαση</option>
                            <option value="without">Μόνο χωρίς σύνδεση σε σύμβαση</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </label>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Γρήγορη περίοδος:</span>
                <x-filament::button size="sm" color="gray" wire:click="setDatePreset('year_to_date')">Έτος έως σήμερα</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="setDatePreset('current_month')">Τρέχων μήνας</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="setDatePreset('previous_month')">Προηγούμενος μήνας</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="setDatePreset('previous_year')">Προηγούμενο έτος</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="setDatePreset('last_12_months')">Τελευταίοι 12 μήνες</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="clearFilters">Καθαρισμός φίλτρων</x-filament::button>
                <label class="ml-auto inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="comparePreviousYear" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Σύγκριση με πέρυσι
                </label>
            </div>
        </x-filament::section>

        @livewire(\App\Filament\Widgets\SampleStatisticsOverviewWidget::class, [
            'summary' => $statistics['summary'],
            'compare' => $statistics['compare'],
            'comparisonLabel' => $comparisonLabel,
        ], key('sample-statistics-overview'))

        @livewire(\App\Filament\Widgets\SampleStatisticsContractSplitWidget::class, [
            'contractSplit' => $statistics['contract_split'],
        ], key('sample-statistics-contract-split'))

        @livewire(\App\Filament\Widgets\SampleStatisticsTrendChart::class, [
            'trend' => $statistics['monthly_trend'],
            'compare' => $statistics['compare'],
            'periodDescription' => 'Παραληφθέντα δείγματα ανά μήνα για ' . $currentLabel . ($statistics['compare'] ? ' και την αντίστοιχη περσινή περίοδο ' . $comparisonLabel : ''),
        ], key('sample-statistics-trend'))

        @livewire(\App\Filament\Widgets\SampleStatisticsDimensionChart::class, [
            'rows' => $statistics['sample_categories'],
            'compare' => $statistics['compare'],
            'dimension' => 'sample',
        ], key('sample-statistics-sample-category-chart'))

        @livewire(\App\Filament\Widgets\SampleStatisticsDimensionChart::class, [
            'rows' => $statistics['customer_categories'],
            'compare' => $statistics['compare'],
            'dimension' => 'customer_category',
        ], key('sample-statistics-customer-category-chart'))

        @livewire(\App\Filament\Widgets\SampleStatisticsTableWidget::class, [
            'rows' => $statistics['sample_categories'],
            'compare' => $statistics['compare'],
            'dimension' => 'sample',
        ], key('sample-statistics-sample-category-table'))

        @livewire(\App\Filament\Widgets\SampleStatisticsTableWidget::class, [
            'rows' => $statistics['customer_categories'],
            'compare' => $statistics['compare'],
            'dimension' => 'customer_category',
        ], key('sample-statistics-customer-category-table'))

        @if (! $this->customerId)
            @livewire(\App\Filament\Widgets\SampleStatisticsTableWidget::class, [
                'rows' => $statistics['top_customers'],
                'compare' => $statistics['compare'],
                'dimension' => 'customer',
            ], key('sample-statistics-top-customers-table'))
        @endif
    </div>
</x-filament-panels::page>

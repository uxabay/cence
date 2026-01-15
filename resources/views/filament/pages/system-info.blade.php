<x-filament-panels::page>
    <x-filament::section>
        <div class="space-y-1">
            <div class="text-lg font-semibold">{{ $appName }}</div>
            <div class="text-sm text-gray-600">
                Version <span class="font-mono">{{ $version }}</span>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Environment</dt><dd class="font-mono">{{ $env }}</dd></div>
            <div><dt class="text-gray-500">Debug</dt><dd class="font-mono">{{ $debug ? 'true' : 'false' }}</dd></div>
            <div><dt class="text-gray-500">PHP</dt><dd class="font-mono">{{ $php }}</dd></div>
            <div><dt class="text-gray-500">Laravel</dt><dd class="font-mono">{{ $laravel }}</dd></div>
            <div><dt class="text-gray-500">Database</dt><dd class="font-mono">{{ $db }} ({{ $dbDriver }})</dd></div>
            <div><dt class="text-gray-500">Timezone</dt><dd class="font-mono">{{ $timezone }}</dd></div>
            <div><dt class="text-gray-500">Locale</dt><dd class="font-mono">{{ $locale }}</dd></div>
        </dl>
    </x-filament::section>
</x-filament-panels::page>


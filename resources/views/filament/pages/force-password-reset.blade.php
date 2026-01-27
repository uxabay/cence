<x-filament-panels::page>
    <div class="flex min-h-[70vh] items-center justify-center">
        <div class="w-small max-w-md">
            <x-filament::section>
                <x-slot name="heading">
                    Υποχρεωτική Αλλαγή Κωδικού
                </x-slot>

                <x-slot name="description">
                    Για λόγους ασφαλείας, ο λογαριασμός σας απαιτεί αλλαγή κωδικού
                    πριν αποκτήσετε πρόσβαση στην εφαρμογή.
                </x-slot>

                <form wire:submit.prevent="save" class="space-y-6 mt-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Νέος Κωδικός
                        </label>

                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model.defer="password"
                                required
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Επιβεβαίωση Κωδικού
                        </label>

                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model.defer="password_confirmation"
                                required
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <x-filament::button type="submit" color="primary">
                            Αποθήκευση
                        </x-filament::button>

                        <x-filament::button
                            color="gray"
                            outlined
                            wire:click="logout"
                        >
                            Αποσύνδεση
                        </x-filament::button>
                    </div>
                </form>

            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>

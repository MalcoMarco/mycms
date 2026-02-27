<x-layouts::app.tenantSidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.tenantSidebar>

<?php

use App\Models\MediaFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Layout('layouts::tenantsApp')]
    public string $tenantId = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = 'all';

    #[Url]
    public string $viewMode = 'grid';

    public function mount(): void
    {
        $this->tenantId = (string) tenant('id');

        if ($this->tenantId === '') {
            abort(404);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        if (! in_array($mode, ['grid', 'list'], true)) {
            return;
        }

        $this->viewMode = $mode;
    }

    public function deleteFile(int $fileId): void
    {
        $file = MediaFile::query()
            ->where('tenant_id', $this->tenantId)
            ->findOrFail($fileId);

        if ($file->storage_path !== '') {
            Storage::disk('s3')->delete($file->storage_path);
        }

        $file->delete();

        session()->flash('message', 'Archivo eliminado correctamente.');
    }

    public function getFilesProperty(): LengthAwarePaginator
    {
        return MediaFile::query()
            ->where('tenant_id', $this->tenantId)
            ->when($this->typeFilter !== 'all', function ($query) {
                $query->where('file_type', $this->typeFilter);
            })
            ->when($this->search !== '', function ($query) {
                $query->where('file_name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(12);
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB'];
        $value = $bytes / 1024;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return number_format($value, 2) . ' ' . $units[$unitIndex];
    }

    public function getTotalUploadedMbProperty(): string
    {
        $totalBytes = (int) MediaFile::query()
            ->where('tenant_id', $this->tenantId)
            ->sum('file_size');

        return number_format($totalBytes / 1024 / 1024, 2);
    }
};
?>

<div>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <flux:heading size="xl">Archivos subidos</flux:heading>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Visualiza y filtra imagenes y PDFs guardados en S3.</p>
                <p class="mt-2 inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    Total subido: {{ $this->totalUploadedMb }} MB
                </p>
            </div>

            <flux:button variant="primary" :href="route('tenants.media.upload')" wire:navigate>
                Subir nuevos archivos
            </flux:button>
        </div>

        @if (session()->has('message'))
            <flux:callout icon="check-circle" variant="success">
                {{ session('message') }}
            </flux:callout>
        @endif

        <div class="grid grid-cols-1 gap-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 md:grid-cols-3">
            <flux:field>
                <flux:label>Buscar por nombre</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="ej: brochure" icon="magnifying-glass" />
            </flux:field>

            <flux:field>
                <flux:label>Tipo de archivo</flux:label>
                <flux:select wire:model.live="typeFilter">
                    <option value="all">Todos</option>
                    <option value="image">Imagenes</option>
                    <option value="pdf">PDF</option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Vista</flux:label>
                <div class="flex gap-2">
                    <flux:button type="button" variant="{{ $viewMode === 'grid' ? 'primary' : 'ghost' }}" wire:click="setViewMode('grid')">
                        Cuadricula
                    </flux:button>
                    <flux:button type="button" variant="{{ $viewMode === 'list' ? 'primary' : 'ghost' }}" wire:click="setViewMode('list')">
                        Lista
                    </flux:button>
                </div>
            </flux:field>
        </div>

        @if ($this->files->isEmpty())
            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-10 text-center dark:border-zinc-600 dark:bg-zinc-900/50">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No hay archivos para mostrar con los filtros actuales.</p>
            </div>
        @else
            @if ($viewMode === 'grid')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->files as $file)
                        <article wire:key="file-grid-{{ $file->id }}" x-data="{ copied: false }" class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                            @if ($file->file_type === 'image')
                                <img src="{{ $file->file_url }}" alt="{{ $file->file_name }}" class="h-44 w-full object-cover" loading="lazy" />
                            @else
                                <div class="flex h-44 items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon.document class="size-14 text-zinc-500" />
                                </div>
                            @endif

                            <div class="space-y-3 p-4">
                                <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100" title="{{ $file->file_name }}">{{ $file->file_name }}</p>
                                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="rounded-full bg-zinc-100 px-2 py-1 font-medium uppercase dark:bg-zinc-800">{{ $file->file_type }}</span>
                                    <span>{{ $this->formatBytes($file->file_size) }}</span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $file->file_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                        Abrir
                                    </a>

                                    <button type="button" x-on:click="navigator.clipboard.writeText('{{ $file->file_url }}'); copied = true; setTimeout(() => copied = false, 1500)" class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                        <span x-show="!copied">Copiar URL</span>
                                        <span x-show="copied">Copiado</span>
                                    </button>

                                    <button type="button" wire:click="deleteFile({{ $file->id }})" wire:confirm="¿Seguro que quieres eliminar este archivo?" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-950/50">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->files as $file)
                            <li wire:key="file-list-{{ $file->id }}" x-data="{ copied: false }" class="flex flex-col gap-3 p-4 md:flex-row md:items-center md:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if ($file->file_type === 'image')
                                        <img src="{{ $file->file_url }}" alt="{{ $file->file_name }}" class="size-12 rounded-lg object-cover" loading="lazy" />
                                    @else
                                        <div class="flex size-12 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                            <flux:icon.document class="size-6 text-zinc-500" />
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $file->file_name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ strtoupper($file->file_type) }} | {{ $this->formatBytes($file->file_size) }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $file->file_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-lg bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">Abrir</a>

                                    <button type="button" x-on:click="navigator.clipboard.writeText('{{ $file->file_url }}'); copied = true; setTimeout(() => copied = false, 1500)" class="inline-flex items-center rounded-lg bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                                        <span x-show="!copied">Copiar URL</span>
                                        <span x-show="copied">Copiado</span>
                                    </button>

                                    <button type="button" wire:click="deleteFile({{ $file->id }})" wire:confirm="¿Seguro que quieres eliminar este archivo?" class="inline-flex items-center rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-950/50">
                                        Eliminar
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                {{ $this->files->links() }}
            </div>
        @endif
    </div>
</div>
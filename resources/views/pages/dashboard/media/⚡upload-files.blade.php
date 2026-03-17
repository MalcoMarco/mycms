<?php

use App\Models\MediaFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    #[Layout('layouts::tenantsApp')]
    public array $files = [];

    public string $tenantId = '';

    public string $disk = 's3';

    public function mount(): void
    {
        $this->tenantId = (string) tenant('id');

        if ($this->tenantId === '') {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'files.required' => 'Selecciona al menos un archivo.',
            'files.*.mimes' => 'Solo se permiten archivos de imagen (jpg, jpeg, png, webp) o PDF.',
            'files.*.max' => 'Cada archivo no debe superar los 10MB.',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        foreach ($validated['files'] as $file) {
            $path = $file->store("{$this->tenantId}/media", $this->disk);
            $mimeType = (string) $file->getMimeType();

            MediaFile::create([
                'tenant_id' => $this->tenantId,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => str_starts_with($mimeType, 'image/') ? 'image' : 'pdf',
                'file_size' => (int) $file->getSize(),
                'file_url' => Storage::disk($this->disk)->url($path),
                'storage_path' => $path,
            ]);
        }

        $uploadedCount = count($validated['files']);

        $this->reset('files');
        $this->resetValidation();

        session()->flash('message', $uploadedCount . ' archivo(s) subido(s) correctamente.');
    }
};
?>

<div>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <flux:heading size="xl">Subir archivos</flux:heading>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Acepta imagenes y PDFs.</p>
        </div>

        @if (session()->has('message'))
            <flux:callout icon="check-circle" variant="success">
                {{ session('message') }}
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:label class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Archivos</flux:label>

            <label for="media-files" class="group block cursor-pointer rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50/70 p-8 text-center transition hover:border-primary-400 hover:bg-primary-50/60 dark:border-zinc-600 dark:bg-zinc-950/40 dark:hover:border-primary-500 dark:hover:bg-primary-950/20">
                <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-full bg-white text-zinc-500 shadow-sm transition group-hover:text-primary-600 dark:bg-zinc-900 dark:text-zinc-400 dark:group-hover:text-primary-400">
                    <flux:icon.arrow-up-tray class="size-7" />
                </div>

                <p class="text-base font-semibold text-zinc-800 dark:text-zinc-100">Arrastra y suelta tus archivos aqui</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">o haz clic para seleccionarlos</p>
                <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">Formatos permitidos: JPG, JPEG, PNG, WEBP y PDF. Maximo 10MB por archivo.</p>
            </label>

            <input
                id="media-files"
                type="file"
                wire:model="files"
                multiple
                accept="image/*,application/pdf"
                class="sr-only"
            />

            <flux:error name="files" />
            <flux:error name="files.*" />

            <div wire:loading wire:target="files" class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300">
                Procesando archivos seleccionados...
            </div>

            @if (! empty($files))
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Archivos seleccionados</p>
                        <span class="rounded-full bg-primary-100 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                            {{ count($files) }} archivo(s)
                        </span>
                    </div>

                    <ul class="space-y-2">
                        @foreach ($files as $selectedFile)
                            <li wire:key="selected-{{ $selectedFile->getFilename() }}" class="flex items-center gap-2 rounded-lg bg-zinc-50 px-3 py-2 text-sm text-zinc-700 dark:bg-zinc-800/70 dark:text-zinc-200">
                                <flux:icon.document class="size-4 text-zinc-500 dark:text-zinc-400" />
                                <span class="truncate">{{ $selectedFile->getClientOriginalName() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save,files">
                    Subir archivos
                </flux:button>

                <flux:button variant="ghost" :href="route('tenants.media.files')" wire:navigate>
                    Ver archivos subidos
                </flux:button>
            </div>
        </form>
    </div>
</div>
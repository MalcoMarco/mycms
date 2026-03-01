<?php

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Layout('layouts::tenantsApp')]

    #[Locked]
    public string $tenantId = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    /** @var array<int, int> */
    public array $selectedPosts = [];
    public bool $selectAll = false;
    public string $bulkAction = '';

    // Modal form
    public bool $showModal = false;
    public ?int $editingPostId = null;
    public string $formTitle = '';
    public string $formSlug = '';

    public string $pageMainSlug = 'home';

    /** @var array<int, string> */
    public const RESERVED_SLUGS = [
        'api',
        'dashboard',
        'admin',
        'login',
        'logout',
        'register',
        'password',
        'reset-password',
        'verify-email',
        'two-factor',
        'user',
        'users',
        'settings',
        'profile',
        'search',
        'sitemap',
        'feed',
        'rss',
    ];

    public function mount(): void
    {
        $this->tenantId = tenant('id');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedPosts = $this->getPostsQuery()->pluck('id')->all();
        } else {
            $this->selectedPosts = [];
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->editingPostId = null;
        $this->formTitle = '';
        $this->formSlug = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $postId): void
    {
        $post = Post::where('tenant_id', $this->tenantId)->findOrFail($postId);

        $this->editingPostId = $post->id;
        $this->formTitle = $post->title ?? '';
        $this->formSlug = $post->slug;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $uniqueSlugRule = \Illuminate\Validation\Rule::unique('posts', 'slug')
            ->where('tenant_id', $this->tenantId)
            ->where('type_id', PostType::Page->value);

        if ($this->editingPostId) {
            $uniqueSlugRule->ignore($this->editingPostId);
        }

        $validated = $this->validate([
            'formTitle' => ['required', 'string', 'max:255'],
            'formSlug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                \Illuminate\Validation\Rule::notIn(self::RESERVED_SLUGS),
                $uniqueSlugRule,
            ],
        ], [
            'formTitle.required' => 'El título es obligatorio.',
            'formSlug.required' => 'El slug es obligatorio.',
            'formSlug.regex' => 'Solo letras minúsculas, números y guiones.',
            'formSlug.not_in' => 'Este slug está reservado y no puede ser utilizado.',
            'formSlug.unique' => 'Ya existe una página con este slug.',
        ]);

        if ($this->editingPostId) {
            $post = Post::where('tenant_id', $this->tenantId)->findOrFail($this->editingPostId);
            $post->update([
                'title' => $validated['formTitle'],
                'slug' => $validated['formSlug'],
            ]);
            session()->flash('message', 'Página actualizada exitosamente.');
        } else {
            Post::create([
                'tenant_id' => $this->tenantId,
                'title' => $validated['formTitle'],
                'slug' => $validated['formSlug'],
                'type_id' => PostType::Page->value,
                'status' => PostStatus::Draft->value,
            ]);
            session()->flash('message', 'Página creada exitosamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function updateStatus(int $postId, string $status): void
    {
        $post = Post::where('tenant_id', $this->tenantId)->findOrFail($postId);
        $post->update(['status' => $status]);
    }

    public function duplicatePost(int $postId): void
    {
        $post = Post::where('tenant_id', $this->tenantId)->findOrFail($postId);

        Post::create([
            'tenant_id' => $this->tenantId,
            'title' => $post->title . ' (copia)',
            'slug' => $post->slug . '-copia-' . time(),
            'type_id' => $post->getRawOriginal('type_id'),
            'status' => PostStatus::Draft->value,
            'content_head' => $post->content_head,
            'content_body' => $post->content_body,
            'content_css' => $post->content_css,
            'content_js' => $post->content_js,
            'excerpt' => $post->excerpt,
        ]);

        session()->flash('message', 'Página duplicada exitosamente.');
    }

    public function deletePost(int $postId): void
    {
        Post::where('tenant_id', $this->tenantId)->where('id', $postId)->delete();
        $this->selectedPosts = array_values(array_diff($this->selectedPosts, [$postId]));

        session()->flash('message', 'Página eliminada exitosamente.');
    }

    public function applyBulkAction(): void
    {
        if (empty($this->bulkAction) || empty($this->selectedPosts)) {
            return;
        }

        $query = Post::where('tenant_id', $this->tenantId)->whereIn('id', $this->selectedPosts);

        match ($this->bulkAction) {
            'publish' => $query->update(['status' => PostStatus::Published->value]),
            'draft' => $query->update(['status' => PostStatus::Draft->value]),
            'archive' => $query->update(['status' => PostStatus::Archived->value]),
            'delete' => $query->delete(),
            default => null,
        };

        $this->selectedPosts = [];
        $this->selectAll = false;
        $this->bulkAction = '';

        session()->flash('message', 'Acción aplicada exitosamente.');
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Post>
     */
    private function getPostsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Post::query()
            ->where('tenant_id', $this->tenantId)
            ->ofType(PostType::Page)
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('slug', 'like', '%' . $this->search . '%')
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function rendering(): void
    {
        $this->selectAll = false;
    }

    public function with(): array
    {
        return [
            'posts' => $this->getPostsQuery()->paginate(15),
            'statusLabels' => [
                PostStatus::Draft->value => 'Borrador',
                PostStatus::Published->value => 'Publicado',
                PostStatus::Archived->value => 'Archivado',
            ],
        ];
    }

    private function resetForm(): void
    {
        $this->editingPostId = null;
        $this->formTitle = '';
        $this->formSlug = '';
        $this->resetValidation();
    }
};
?>

<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/50 dark:text-green-300">
            {{ session('message') }}
        </div>
    @endif

    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-8 flex items-center justify-between">
            <flux:heading size="xl">Lista de Páginas</flux:heading>
            <flux:button variant="primary" wire:click="openCreateModal" icon="plus">
                Nueva Página
            </flux:button>
        </div>

        {{-- Search and Filters --}}
        <div class="mb-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-col gap-4 sm:flex-row">
                    {{-- Search Input --}}
                    <div class="flex-1">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por título o slug..."
                            icon="magnifying-glass"
                            clearable
                        />
                    </div>

                    {{-- Status Filter --}}
                    <div class="sm:w-48">
                        <flux:select wire:model.live="statusFilter" placeholder="Todos los estados">
                            <flux:select.option value="">Todos los estados</flux:select.option>
                            <flux:select.option value="published">Publicado</flux:select.option>
                            <flux:select.option value="draft">Borrador</flux:select.option>
                            <flux:select.option value="archived">Archivado</flux:select.option>
                        </flux:select>
                    </div>

                    {{-- Results count --}}
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                        @if ($posts->total() > 0)
                            {{ $posts->total() }} página{{ $posts->total() !== 1 ? 's' : '' }} encontrada{{ $posts->total() !== 1 ? 's' : '' }}
                        @else
                            No se encontraron páginas
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Posts Table --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            {{-- Table Header with Bulk Actions --}}
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <flux:checkbox wire:model.live="selectAll" />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Seleccionar todo ({{ count($selectedPosts) }})
                            </span>
                        </div>

                        @if (count($selectedPosts) > 0)
                            <div class="flex items-center space-x-2">
                                <flux:select wire:model="bulkAction" size="sm" placeholder="Acción...">
                                    <flux:select.option value="publish">Publicar</flux:select.option>
                                    <flux:select.option value="draft">Borrador</flux:select.option>
                                    <flux:select.option value="archive">Archivar</flux:select.option>
                                    <flux:select.option value="delete">Eliminar</flux:select.option>
                                </flux:select>
                                <flux:button size="sm" wire:click="applyBulkAction">Aplicar</flux:button>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $posts->count() }} elementos mostrados de {{ $posts->total() }}
                        </span>
                        @if ($search || $statusFilter)
                            <button wire:click="clearFilters" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                <flux:icon.x-mark class="mr-1 inline size-3" />
                                Limpiar filtros
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="w-8 px-6 py-3"></th>
                            <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" wire:click="sortBy('title')">
                                <div class="flex items-center space-x-1">
                                    <span>Título</span>
                                    @if ($sortField === 'title')
                                        <flux:icon.chevron-up class="size-3" :class="$sortDirection === 'desc' ? 'rotate-180' : ''" />
                                    @else
                                        <flux:icon.chevron-up-down class="size-3" />
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" wire:click="sortBy('slug')">
                                <div class="flex items-center space-x-1">
                                    <span>Slug</span>
                                    @if ($sortField === 'slug')
                                        <flux:icon.chevron-up class="size-3" :class="$sortDirection === 'desc' ? 'rotate-180' : ''" />
                                    @else
                                        <flux:icon.chevron-up-down class="size-3" />
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" wire:click="sortBy('status')">
                                <div class="flex items-center space-x-1">
                                    <span>Estado</span>
                                    @if ($sortField === 'status')
                                        <flux:icon.chevron-up class="size-3" :class="$sortDirection === 'desc' ? 'rotate-180' : ''" />
                                    @else
                                        <flux:icon.chevron-up-down class="size-3" />
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" wire:click="sortBy('created_at')">
                                <div class="flex items-center space-x-1">
                                    <span>Fecha</span>
                                    @if ($sortField === 'created_at')
                                        <flux:icon.chevron-up class="size-3" :class="$sortDirection === 'desc' ? 'rotate-180' : ''" />
                                    @else
                                        <flux:icon.chevron-up-down class="size-3" />
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @forelse ($posts as $post)
                            <tr
                                wire:key="post-{{ $post->id }}"
                                class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700 {{ in_array($post->id, $selectedPosts) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                            >
                                {{-- Checkbox --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <flux:checkbox wire:model.live="selectedPosts" value="{{ $post->id }}" />
                                </td>

                                {{-- Title --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex flex-col">
                                        <div class="cursor-pointer text-sm font-medium text-gray-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400">
                                            @if ($post->slug === $pageMainSlug)
                                                <flux:icon.home class="inline size-4" />
                                            @endif
                                            {{ $post->title }}
                                        </div>
                                        <div class="mt-1 flex items-center space-x-3 text-xs text-gray-500 dark:text-gray-400">
                                            <a href="{{ route('tenants.posts.page-builder', $post->slug) }}" target="_blank" class="hover:text-blue-600 dark:hover:text-blue-400">Editar web</a>
                                            <span>|</span>
                                            <a href="{{ route('tenants.posts.preview', $post->slug) }}" target="_blank" class="hover:text-green-600 dark:hover:text-green-400">Vista rápida</a>
                                            @if ($post->getRawOriginal('status') !== 'archived')
                                                <span>|</span>
                                                <button wire:click="updateStatus({{ $post->id }}, 'archived')" class="hover:text-red-600 dark:hover:text-red-400">Archivar</button>
                                            @endif
                                            @if ($post->getRawOriginal('status') !== 'draft')
                                                <span>|</span>
                                                <button wire:click="updateStatus({{ $post->id }}, 'draft')" class="hover:text-yellow-600 dark:hover:text-yellow-400">Borrador</button>
                                            @endif
                                            @if ($post->getRawOriginal('status') !== 'published')
                                                <span>|</span>
                                                <button wire:click="updateStatus({{ $post->id }}, 'published')" class="hover:text-green-600 dark:hover:text-green-400">Publicar</button>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Slug --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="rounded bg-gray-100 px-2 py-1 font-mono text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        {{ $post->slug === $pageMainSlug ? '(Página Principal)' : $post->slug }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="whitespace-nowrap px-6 py-4">
                                    @php $rawStatus = $post->getRawOriginal('status'); @endphp
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $rawStatus === 'published',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $rawStatus === 'draft',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $rawStatus === 'archived',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300' => ! in_array($rawStatus, ['published', 'draft', 'archived']),
                                    ])>
                                        <div @class([
                                            'mr-1.5 h-1.5 w-1.5 rounded-full',
                                            'bg-green-500' => $rawStatus === 'published',
                                            'bg-yellow-500' => $rawStatus === 'draft',
                                            'bg-red-500' => $rawStatus === 'archived',
                                            'bg-gray-500' => ! in_array($rawStatus, ['published', 'draft', 'archived']),
                                        ])></div>
                                        {{ $statusLabels[$rawStatus] ?? 'Desconocido' }}
                                    </span>
                                </td>

                                {{-- Date --}}
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col">
                                        <span>{{ $post->created_at->format('d/m/Y') }}</span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $post->created_at->format('H:i') }}</span>
                                    </div>
                                </td>

                                {{-- Actions --}}
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="openEditModal({{ $post->id }})" title="Editar" />
                                        <flux:button variant="ghost" size="sm" icon="document-duplicate" wire:click="duplicatePost({{ $post->id }})" title="Duplicar" class="!text-green-600 hover:!text-green-900 dark:!text-green-400" />
                                        <flux:button variant="ghost" size="sm" icon="trash" wire:click="deletePost({{ $post->id }})" wire:confirm="¿Estás seguro de eliminar esta página?" title="Eliminar" class="!text-red-600 hover:!text-red-900 dark:!text-red-400" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="mx-auto mb-4 h-24 w-24 text-gray-400 dark:text-gray-500">
                                        <flux:icon.document-text class="mx-auto size-16" />
                                    </div>
                                    <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">No hay páginas creadas</h3>
                                    <p class="mb-6 text-gray-500 dark:text-gray-400">Comienza creando tu primera página</p>
                                    <flux:button variant="primary" wire:click="openCreateModal" icon="plus">
                                        Crear Primera Página
                                    </flux:button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($posts->hasPages())
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Create / Edit Modal --}}
    <flux:modal wire:model.self="showModal" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingPostId ? 'Actualizar Página' : 'Crear Página' }}</flux:heading>
            </div>

            <flux:field>
                <flux:label>Título</flux:label>
                <flux:input wire:model="formTitle" placeholder="Ingresa el título de la página" />
                <flux:error name="formTitle" />
            </flux:field>

            <flux:field>
                <flux:label>Slug</flux:label>
                <flux:input wire:model="formSlug" placeholder="url-amigable-de-la-pagina" class="font-mono text-sm" />
                <flux:error name="formSlug" />
                <flux:text size="sm" class="mt-1">
                    Solo letras minúsculas, números y guiones (-). Usar "{{ $pageMainSlug }}" para la página principal.
                </flux:text>
            </flux:field>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showModal', false)">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="check">
                    {{ $editingPostId ? 'Actualizar' : 'Crear' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>

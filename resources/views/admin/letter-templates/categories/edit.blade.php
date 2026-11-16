{{-- resources/views/admin/letter-templates/categories/edit.blade.php --}}
<x-admin-layout title="Edit letter category">
    @section('page_header', 'Edit letter category')

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="ui-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Edit category</h2>
            <p class="text-sm text-gray-500 mb-4">Update the category details.</p>

            <form method="POST" action="{{ route('letter-categories.update', $category) }}" class="space-y-6">
                @csrf
                @method('PATCH')
                @include('admin.letter-templates.categories._form', ['category' => $category])
                <div class="flex items-center justify-between">
                    <a href="{{ route('letter-categories.index') }}"
                        class="ui-back"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
Back</a>
                    <button type="submit"
                        class="btn btn-primary">Update category</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

{{-- resources/views/admin/letter-templates/categories/create.blade.php --}}
<x-admin-layout title="New letter category">
    @section('page_header', 'New letter category')

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="ui-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Create category</h2>
            <p class="text-sm text-gray-500 mb-4">Add a category to organize letter templates.</p>

            <form method="POST" action="{{ route('letter-categories.store') }}" class="space-y-6">
                @csrf
                @include('admin.letter-templates.categories._form', ['category' => $category])
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('letter-categories.index') }}"
                        class="btn btn-secondary">Cancel</a>
                    <button type="submit"
                        class="btn btn-success">Save category</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

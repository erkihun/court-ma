<x-admin-layout title="{{ __('about.title') }}">
    @section('page_header', __('about.title'))

    <div class="ui-card p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('about.title') }}</h1>
                <p class="text-sm text-gray-600 max-w-2xl">{{ __('about.manage_description') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('about.create') }}"
                    class="btn btn-primary">
                    {{ __('about.create_new') }}
                </a>
            </div>
        </div>

        @if($aboutPages->isEmpty())
        <div class="ui-empty text-center">
            <div class="text-lg font-semibold text-gray-900">{{ __('about.empty_state') }}</div>
            <p class="text-sm text-gray-600 mt-2">{{ __('about.empty_state_help') }}</p>
            <div class="mt-4">
                <a href="{{ route('about.create') }}"
                    class="btn btn-primary">
                    {{ __('about.create_new') }}
                </a>
            </div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="ui-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('about.table_title') }}</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('about.table_status') }}</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('about.table_updated') }}</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-700">{{ __('about.table_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($aboutPages as $about)
                    <tr>
                        <td class="px-5 py-4 text-sm text-gray-900">
                            <div class="font-semibold">{{ $about->title }}</div>
                            <div class="text-xs text-gray-500">/{{ $about->slug }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            @if($about->is_published)
                            <span class="ui-badge ui-badge-emerald inline-flex items-center">
                                {{ __('about.status_published') }}
                            </span>
                            @else
                            <span class="ui-badge ui-badge-slate inline-flex items-center">
                                {{ __('about.status_draft') }}
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600" data-sort-value="{{ $about->updated_at }}">
                            {{ \App\Support\EthiopianDate::smartFormat($about->updated_at, true, '', 'h:i A', 'Y-m-d') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('about.show', $about) }}"
                                    class="text-blue-700 hover:text-blue-800 text-sm">
                                    {{ __('about.action_view') }}
                                </a>
                                <a href="{{ route('about.edit', $about) }}"
                                    class="text-amber-700 hover:text-amber-800 text-sm">
                                    {{ __('about.action_edit') }}
                                </a>
                                <form action="{{ route('about.destroy', $about) }}" method="POST" class="inline"
                                    onsubmit="return confirm('{{ __('about.confirm_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:text-red-800 text-sm">
                                        {{ __('about.action_delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $aboutPages->links() }}
        </div>
        @endif
    </div>
</x-admin-layout>

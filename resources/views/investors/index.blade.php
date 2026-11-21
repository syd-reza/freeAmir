<x-app-layout :title="__('Investors')">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ __('Investors') }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">{{ __('Manage investors, their capital and profit share.') }}</p>
        </div>
        <a href="{{ route('investors.create') }}" class="btn btn-primary btn-sm">{{ __('Create investor') }}</a>
    </div>

    <div class="mx-1 mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Total invested capital') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($totalCapital) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Active investors') }}</span>
                <span class="text-2xl font-bold">{{ localizeNumber($investors->where('is_active', true)->count()) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <a href="{{ route('profit-settlements.index') }}" class="link link-primary text-sm">{{ __('Weekly profit settlements') }}</a>
            </div>
        </div>
    </div>

    <div class="card mx-1 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Phone') }}</th>
                            <th class="px-4 py-3">{{ __('Capital') }}</th>
                            <th class="px-4 py-3">{{ __('Profit share percent') }}</th>
                            <th class="px-4 py-3">{{ __('Transactions') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investors as $investor)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('investors.show', $investor) }}" class="link link-primary">{{ $investor->name }}</a>
                                </td>
                                <td class="px-4 py-3" dir="ltr">{{ $investor->phone }}</td>
                                <td class="px-4 py-3">{{ formatNumber($investor->capital_amount) }}</td>
                                <td class="px-4 py-3">{{ localizeNumber($investor->share_percent) }}%</td>
                                <td class="px-4 py-3">{{ localizeNumber($investor->capital_transactions_count) }}</td>
                                <td class="px-4 py-3">
                                    <span @class(['badge badge-sm', 'badge-success' => $investor->is_active, 'badge-ghost' => ! $investor->is_active])>
                                        {{ $investor->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a href="{{ route('investors.show', $investor) }}" class="btn btn-xs btn-info">{{ __('View') }}</a>
                                        <a href="{{ route('investors.edit', $investor) }}" class="btn btn-xs btn-warning">{{ __('Edit') }}</a>
                                        <form action="{{ route('investors.destroy', $investor) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-error">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-base-content/50">{{ __('No investors have been registered yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

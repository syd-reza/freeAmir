<x-app-layout :title="__('Investor') . ' - ' . $investor->name">
    <x-show-message-bags />

    <div class="flex flex-wrap items-center justify-between gap-4 px-1 pb-5">
        <div>
            <h1 class="text-xl font-bold text-base-content">{{ $investor->name }}</h1>
            <p class="mt-0.5 text-sm text-base-content/50">{{ __('Investor capital and history') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('investors.index') }}" class="btn btn-ghost btn-sm">{{ __('Back') }}</a>
            <a href="{{ route('investors.edit', $investor) }}" class="btn btn-warning btn-sm">{{ __('Edit') }}</a>
        </div>
    </div>

    <div class="mx-1 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Current capital') }}</span>
                <span class="text-2xl font-bold">{{ formatNumber($investor->capital_amount) }}</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Profit share percent') }}</span>
                <span class="text-2xl font-bold">{{ localizeNumber($investor->share_percent) }}%</span>
            </div>
        </div>
        <div class="card border border-base-200 bg-base-100 shadow-sm">
            <div class="card-body py-4">
                <span class="text-sm text-base-content/60">{{ __('Phone') }}</span>
                <span class="text-lg font-semibold" dir="ltr">{{ $investor->phone ?: '-' }}</span>
            </div>
        </div>
    </div>

    <div class="card mx-1 mt-4 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base">{{ __('Record capital transaction') }}</h2>
            <form action="{{ route('investors-capital.store', $investor) }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                @csrf
                <label class="fieldset">
                    <span class="label">{{ __('Type') }}</span>
                    <select name="type" class="select select-bordered w-full">
                        <option value="deposit">{{ __('Capital deposit') }}</option>
                        <option value="withdraw">{{ __('Capital withdrawal') }}</option>
                    </select>
                </label>
                <x-input name="amount" title="{{ __('Amount') }}" :value="old('amount')" placeholder="0" />
                <x-input name="date" title="{{ __('Date (Jalali)') }}" :value="old('date')" placeholder="1405/08/25" />
                <x-input name="description" title="{{ __('Description') }}" :value="old('description')" placeholder="{{ __('Optional') }}" />
                <div class="md:col-span-4">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mx-1 mt-4 border border-base-200 bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <h2 class="px-4 pt-4 text-base font-bold">{{ __('Capital history') }}</h2>
            <div class="overflow-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Date') }}</th>
                            <th class="px-4 py-3">{{ __('Type') }}</th>
                            <th class="px-4 py-3">{{ __('Amount') }}</th>
                            <th class="px-4 py-3">{{ __('Description') }}</th>
                            <th class="px-4 py-3">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investor->capitalTransactions as $transaction)
                            <tr>
                                <td class="px-4 py-3">{{ convertToJalali($transaction->date) }}</td>
                                <td class="px-4 py-3">{{ $transaction->typeLabel() }}</td>
                                <td class="px-4 py-3">{{ formatNumber($transaction->amount) }}</td>
                                <td class="px-4 py-3">{{ $transaction->description }}</td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('investors-capital.destroy', [$investor, $transaction]) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-error">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-base-content/50">{{ __('No capital transactions yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div class="col-span-2 md:col-span-1">
        <x-input name="name" id="name" title="{{ __('Name') }}" :value="old('name', $investor->name ?? '')" placeholder="{{ __('Investor name') }}" />
    </div>

    <div class="col-span-2 md:col-span-1">
        <x-input name="phone" id="phone" title="{{ __('Phone') }}" :value="old('phone', $investor->phone ?? '')" placeholder="09xxxxxxxxx" />
    </div>

    <div class="col-span-2 md:col-span-1">
        <x-input name="national_id" id="national_id" title="{{ __('National ID') }}" :value="old('national_id', $investor->national_id ?? '')" placeholder="{{ __('National ID') }}" />
    </div>

    <div class="col-span-2 md:col-span-1">
        <x-input name="share_percent" id="share_percent" title="{{ __('Profit share percent') }}" :value="old('share_percent', $investor->share_percent ?? 50)" placeholder="50" />
    </div>

    @unless ($investor->exists)
        <div class="col-span-2 md:col-span-1">
            <x-input name="initial_capital" id="initial_capital" title="{{ __('Initial capital') }}" :value="old('initial_capital', 0)" placeholder="0" />
        </div>
    @endunless

    <div class="col-span-2 md:col-span-1 flex items-end">
        <input type="hidden" name="is_active" value="0">
        <label class="label cursor-pointer gap-3">
            <span class="label-text">{{ __('Active') }}</span>
            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" @checked((bool) old('is_active', $investor->is_active ?? true))>
        </label>
    </div>

    <div class="col-span-2">
        <label class="fieldset w-full">
            <span class="label">{{ __('Notes') }}</span>
            <textarea name="notes" rows="3" class="textarea textarea-bordered w-full">{{ old('notes', $investor->notes ?? '') }}</textarea>
        </label>
    </div>
</div>

@php
    $selectedWarehouseId = old('warehouse_id', $invoice->warehouse_id ?? $warehouses->first()?->id);
    $singleWarehouse = $warehouses->count() === 1 ? $warehouses->first() : null;
@endphp

<div class="flex flex-wrap {{ $warehouseClass ?? 'w-full xl:w-1/4' }}">
    <label class="text-gray-500 w-full" for="warehouse_id">{{ __('Warehouse') }}</label>

    @if ($singleWarehouse)
        <input type="hidden" name="warehouse_id" value="{{ $singleWarehouse->id }}">
        <div class="input input-bordered flex w-full items-center bg-base-200/50" id="warehouse_id">
            {{ $singleWarehouse->name }}
        </div>
    @else
        <select name="warehouse_id" id="warehouse_id" class="select select-bordered w-full" required>
            <option value="">{{ __('Select Warehouse') }}</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected((int) $selectedWarehouseId === (int) $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    @endif
</div>

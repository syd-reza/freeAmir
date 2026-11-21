<x-app-layout :title="__('Edit investor')">
    <x-show-message-bags />

    <div class="px-1 pb-5">
        <h1 class="text-xl font-bold text-base-content">{{ __('Edit investor') }} - {{ $investor->name }}</h1>
    </div>

    <form action="{{ route('investors.update', $investor) }}" method="POST" class="card mx-1 border border-base-200 bg-base-100 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('investors.form', ['investor' => $investor])

            <div class="mt-4 flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('investors.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</x-app-layout>

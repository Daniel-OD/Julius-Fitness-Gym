<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('app.client_portal.title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-2">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ __('app.client_portal.welcome', ['name' => $member->name]) }}
                </h3>
                <p class="text-sm text-gray-500">
                    {{ __('app.client_portal.member_code', ['code' => $member->code]) }}
                </p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <h4 class="font-medium text-gray-900">{{ __('app.client_portal.subscription') }}</h4>

                @include('client.partials.subscription-badge', ['access' => $access])

                @if ($activeSubscription?->plan)
                    <dl class="grid gap-2 text-sm text-gray-600">
                        <div>
                            <dt class="font-medium text-gray-900">{{ __('app.client_portal.plan') }}</dt>
                            <dd>{{ $activeSubscription->plan->name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900">{{ __('app.client_portal.validity') }}</dt>
                            <dd>
                                {{ __('app.client_portal.period', [
                                    'start' => $activeSubscription->start_date?->format('d.m.Y'),
                                    'end' => $activeSubscription->end_date?->format('d.m.Y'),
                                ]) }}
                            </dd>
                        </div>
                    </dl>
                @endif
            </div>

            @include('client.partials.qr-card', ['qrSvg' => $qrSvg])

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h4 class="font-medium text-gray-900 mb-4">{{ __('app.client_portal.visits') }}</h4>

                @if ($recentCheckIns->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('app.client_portal.no_visits') }}</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($recentCheckIns as $checkIn)
                            <li class="py-3 text-sm text-gray-600">
                                <p>{{ __('app.client_portal.checked_in_at', ['time' => $checkIn->checked_in_at->format('d.m.Y H:i')]) }}</p>
                                <p class="text-gray-500">
                                    @if ($checkIn->checked_out_at)
                                        {{ __('app.client_portal.checked_out_at', ['time' => $checkIn->checked_out_at->format('d.m.Y H:i')]) }}
                                    @else
                                        {{ __('app.client_portal.still_present') }}
                                    @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

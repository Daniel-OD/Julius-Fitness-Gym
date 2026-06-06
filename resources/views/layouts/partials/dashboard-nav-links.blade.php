@php
    $dashboardLinks = [
        'admin' => [
            'href' => \Filament\Facades\Filament::getPanel('admin')->getUrl(),
            'label' => __('app.navigation.dashboard_admin'),
            'active' => request()->is('admin*'),
        ],
        'office' => [
            'href' => \Filament\Facades\Filament::getPanel('office')->getUrl(),
            'label' => __('app.navigation.dashboard_employee'),
            'active' => request()->is('office*'),
        ],
        'client' => [
            'href' => route('client.dashboard'),
            'label' => __('app.navigation.dashboard_client'),
            'active' => request()->routeIs('client.*'),
        ],
    ];
@endphp

@foreach (Auth::user()->accessibleDashboards() as $dashboard)
    @if (isset($dashboardLinks[$dashboard]))
        @php($link = $dashboardLinks[$dashboard])
        <x-nav-link :href="$link['href']" :active="$link['active']">
            {{ $link['label'] }}
        </x-nav-link>
    @endif
@endforeach

@php
    $guide = \App\Support\AdminGuide::forCurrentPage();
@endphp

@if ($guide !== null && ($guide['title'] !== '' || $guide['summary'] !== ''))
    <div
        x-data="{ open: true }"
        class="jf-admin-guide fi-section rounded-xl mb-6"
    >
        <div class="jf-admin-guide__header">
            <div class="jf-admin-guide__icon" aria-hidden="true">
                <x-filament::icon icon="heroicon-o-light-bulb" class="h-5 w-5" />
            </div>

            <div class="jf-admin-guide__intro">
                <div class="jf-admin-guide__badge">{{ __('admin_guide.badge') }}</div>

                @if ($guide['title'] !== '')
                    <h2 class="jf-admin-guide__title">{{ $guide['title'] }}</h2>
                @endif

                @if ($guide['summary'] !== '')
                    <p class="jf-admin-guide__summary">{{ $guide['summary'] }}</p>
                @endif
            </div>

            <button
                type="button"
                class="jf-admin-guide__toggle"
                x-on:click="open = ! open"
                x-bind:aria-expanded="open.toString()"
            >
                <span x-show="open" x-cloak>{{ __('admin_guide.collapse') }}</span>
                <span x-show="! open" x-cloak>{{ __('admin_guide.expand') }}</span>
                <x-filament::icon
                    icon="heroicon-m-chevron-up"
                    class="h-4 w-4 transition-transform duration-200"
                    x-bind:class="{ 'rotate-180': ! open }"
                />
            </button>
        </div>

        <div x-show="open" x-collapse class="jf-admin-guide__body">
            @if ($guide['tips'] !== [])
                <div class="jf-admin-guide__block">
                    <h3 class="jf-admin-guide__heading">{{ __('admin_guide.tips_heading') }}</h3>
                    <ul class="jf-admin-guide__list">
                        @foreach ($guide['tips'] as $tip)
                            <li>{{ $tip }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($guide['widgets'] !== [])
                <div class="jf-admin-guide__block">
                    <h3 class="jf-admin-guide__heading">{{ __('admin_guide.widgets_heading') }}</h3>
                    <dl class="jf-admin-guide__widgets">
                        @foreach ($guide['widgets'] as $label => $description)
                            <div class="jf-admin-guide__widget">
                                <dt>{{ str($label)->replace('_', ' ')->title() }}</dt>
                                <dd>{{ $description }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </div>
@endif

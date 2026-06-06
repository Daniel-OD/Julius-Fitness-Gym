@php
    $storageKey = 'jf-guide-' . str_replace('.', '-', $contextKey);
    $hasSteps = ($guide['steps'] ?? []) !== [];
    $hasChecklist = ($guide['checklist'] ?? []) !== [];
    $hasTips = ($guide['tips'] ?? []) !== [];
    $hasWidgets = ($guide['widgets'] ?? []) !== [];
@endphp

<div
    x-data="{
        open: true,
        openStep: 0,
        storageKey: @js($storageKey),
        checked: [],
        init() {
            try {
                this.checked = JSON.parse(localStorage.getItem(this.storageKey) || '[]')
            } catch (e) {
                this.checked = []
            }
        },
        toggleCheck(index) {
            if (this.checked.includes(index)) {
                this.checked = this.checked.filter(i => i !== index)
            } else {
                this.checked = [...this.checked, index]
            }
            localStorage.setItem(this.storageKey, JSON.stringify(this.checked))
        },
        isChecked(index) {
            return this.checked.includes(index)
        },
        progress(total) {
            if (total === 0) return 0
            return Math.round((this.checked.length / total) * 100)
        },
    }"
    @class([
        'jf-admin-guide fi-section rounded-xl',
        'jf-admin-guide--embedded' => $embedded ?? false,
        'mb-6' => ! ($embedded ?? false),
    ])
>
    <div class="jf-admin-guide__header">
        <div class="jf-admin-guide__icon" aria-hidden="true">
            <x-filament::icon icon="heroicon-o-light-bulb" class="h-5 w-5" />
        </div>

        <div class="jf-admin-guide__intro">
            <div class="jf-admin-guide__badge">{{ __('admin_guide.badge') }}</div>

            @if (($guide['title'] ?? '') !== '')
                <h2 class="jf-admin-guide__title">{{ $guide['title'] }}</h2>
            @endif

            @if (($guide['greeting'] ?? '') !== '')
                <p class="jf-admin-guide__greeting">{{ $guide['greeting'] }}</p>
            @endif

            @if (($guide['summary'] ?? '') !== '')
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
        @if ($hasSteps)
            <div class="jf-admin-guide__block">
                <h3 class="jf-admin-guide__heading">{{ __('admin_guide.steps_heading') }}</h3>

                <div class="jf-admin-guide__steps">
                    @foreach ($guide['steps'] as $index => $step)
                        <div class="jf-admin-guide__step" x-bind:class="{ 'is-open': openStep === {{ $index }} }">
                            <button
                                type="button"
                                class="jf-admin-guide__step-trigger"
                                x-on:click="openStep = openStep === {{ $index }} ? -1 : {{ $index }}"
                                x-bind:aria-expanded="(openStep === {{ $index }}).toString()"
                            >
                                <span class="jf-admin-guide__step-number">{{ $index + 1 }}</span>
                                <span class="jf-admin-guide__step-title">{{ $step['title'] }}</span>
                                <x-filament::icon
                                    icon="heroicon-m-chevron-down"
                                    class="jf-admin-guide__step-chevron h-4 w-4"
                                />
                            </button>

                            <div x-show="openStep === {{ $index }}" x-collapse class="jf-admin-guide__step-body">
                                @if ($step['body'] !== '')
                                    <p class="jf-admin-guide__step-text">{{ $step['body'] }}</p>
                                @endif

                                @if (($step['fields'] ?? []) !== [])
                                    <div class="jf-admin-guide__fields">
                                        @foreach ($step['fields'] as $field)
                                            <div class="jf-admin-guide__field">
                                                <div class="jf-admin-guide__field-name">{{ $field['name'] }}</div>
                                                @if ($field['hint'] !== '')
                                                    <div class="jf-admin-guide__field-hint">{{ $field['hint'] }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($hasChecklist)
            <div class="jf-admin-guide__block">
                <div class="jf-admin-guide__checklist-header">
                    <h3 class="jf-admin-guide__heading">{{ __('admin_guide.checklist_heading') }}</h3>
                    <span class="jf-admin-guide__progress" x-text="progress({{ count($guide['checklist']) }}) + '%'"></span>
                </div>

                <ul class="jf-admin-guide__checklist">
                    @foreach ($guide['checklist'] as $index => $item)
                        <li>
                            <label class="jf-admin-guide__check-item">
                                <input
                                    type="checkbox"
                                    x-bind:checked="isChecked({{ $index }})"
                                    x-on:change="toggleCheck({{ $index }})"
                                />
                                <span>{{ $item }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($hasTips)
            <div class="jf-admin-guide__block">
                <h3 class="jf-admin-guide__heading">{{ __('admin_guide.tips_heading') }}</h3>
                <ul class="jf-admin-guide__list">
                    @foreach ($guide['tips'] as $tip)
                        <li>{{ $tip }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($hasWidgets)
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

        @if (($guide['save_reminder'] ?? '') !== '')
            <div class="jf-admin-guide__save-reminder">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5 shrink-0" />
                <span>{{ $guide['save_reminder'] }}</span>
            </div>
        @endif
    </div>
</div>

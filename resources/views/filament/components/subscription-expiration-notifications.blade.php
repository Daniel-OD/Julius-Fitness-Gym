<div class="fi-topbar-item">
    <x-filament::dropdown placement="bottom-end" teleport width="md">
        <x-slot name="trigger">
            <x-filament::icon-button
                :badge="$unreadCount > 0 ? (string) $unreadCount : null"
                badge-color="warning"
                color="gray"
                icon="heroicon-o-bell"
                icon-size="lg"
                :label="__('app.notifications.expiration_bell_label')"
                class="fi-subscription-expiration-notifications-btn"
            />
        </x-slot>

        <div class="fi-subscription-expiration-notifications-panel">
            <div class="fi-subscription-expiration-notifications-header">
                <div>
                    <p class="fi-subscription-expiration-notifications-title">
                        {{ __('app.notifications.expiration_panel_title') }}
                    </p>
                    <p class="fi-subscription-expiration-notifications-subtitle">
                        {{ __('app.notifications.expiration_panel_subtitle', ['days' => \App\Helpers\Helpers::getSubscriptionExpiringDays()]) }}
                    </p>
                </div>
                @if ($unreadCount > 0)
                    <x-filament::button
                        type="button"
                        color="gray"
                        size="xs"
                        wire:click="markAllAsRead"
                        class="shrink-0"
                    >
                        {{ __('app.notifications.mark_all_read') }}
                    </x-filament::button>
                @endif
            </div>

            @if (count($notifications) === 0)
                <div class="fi-subscription-expiration-notifications-empty">
                    <x-filament::icon
                        icon="heroicon-o-check-circle"
                        class="fi-subscription-expiration-notifications-empty-icon"
                    />
                    <p>{{ __('app.widgets.no_expiring_subscriptions') }}</p>
                </div>
            @else
                <ul class="fi-subscription-expiration-notifications-list" role="list">
                    @foreach ($notifications as $notification)
                        <li
                            class="fi-subscription-expiration-notifications-item {{ $notification['isRead'] ? 'is-read' : '' }}"
                            wire:key="subscription-expiration-notification-{{ $notification['subscriptionId'] }}"
                        >
                            <a
                                href="{{ $notification['url'] }}"
                                wire:click="markAsRead({{ $notification['subscriptionId'] }})"
                                class="fi-subscription-expiration-notifications-link"
                            >
                                @if ($notification['memberPhotoUrl'])
                                    <img
                                        src="{{ $notification['memberPhotoUrl'] }}"
                                        alt=""
                                        class="fi-subscription-expiration-notifications-avatar fi-subscription-expiration-notifications-avatar--photo"
                                    />
                                @else
                                    <span
                                        class="fi-subscription-expiration-notifications-avatar fi-subscription-expiration-notifications-avatar--initials"
                                        aria-hidden="true"
                                    >
                                        {{ $notification['memberInitials'] }}
                                    </span>
                                @endif

                                <span class="fi-subscription-expiration-notifications-body">
                                    <span class="fi-subscription-expiration-notifications-member">
                                        {{ $notification['memberName'] }}
                                    </span>
                                    <span class="fi-subscription-expiration-notifications-plan">
                                        {{ $notification['planName'] }}
                                    </span>
                                </span>

                                <span class="fi-subscription-expiration-notifications-meta">
                                    <span
                                        class="fi-subscription-expiration-notifications-badge fi-subscription-expiration-notifications-badge--{{ $notification['urgency'] }}"
                                    >
                                        {{ $notification['daysLabel'] }}
                                    </span>
                                    <span class="fi-subscription-expiration-notifications-urgency">
                                        {{ $notification['urgencyLabel'] }}
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-filament::dropdown>
</div>

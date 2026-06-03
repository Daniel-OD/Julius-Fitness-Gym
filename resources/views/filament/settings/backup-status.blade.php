@php
    use App\Helpers\Helpers;

    $settings = Helpers::getSettings();
    $backupPath = trim((string) ($settings['backup']['path'] ?? ''));
    $backupPath = $backupPath !== '' ? rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $backupPath), DIRECTORY_SEPARATOR) : '';
    $files = [];

    if ($backupPath !== '' && is_dir($backupPath)) {
        $found = glob($backupPath . DIRECTORY_SEPARATOR . 'julius-gym-backup-*.zip') ?: [];
        rsort($found);
        $files = array_slice($found, 0, 10);
    }
@endphp

@if ($backupPath === '')
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('app.settings.backup.no_path_set') }}
    </p>
@elseif (empty($files))
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('app.settings.backup.no_backups') }}
    </p>
@else
    <div class="space-y-2">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('app.settings.backup.recent_backups') }}
        </p>
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">{{ __('app.settings.backup.file') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">{{ __('app.settings.backup.date') }}</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600 dark:text-gray-400">{{ __('app.settings.backup.size') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach ($files as $index => $file)
                        @php
                            $name = basename($file);
                            $bytes = file_exists($file) ? filesize($file) : 0;
                            $size = $bytes >= 1048576
                                ? number_format($bytes / 1048576, 1) . ' MB'
                                : number_format($bytes / 1024, 1) . ' KB';
                            $modified = filemtime($file);
                            $date = $modified ? date('d.m.Y H:i', $modified) : '—';
                        @endphp
                        <tr class="{{ $index === 0 ? 'bg-green-50 dark:bg-green-950/30' : '' }}">
                            <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                @if ($index === 0)
                                    <x-heroicon-o-star class="w-3 h-3 text-green-500 shrink-0" />
                                @endif
                                {{ $name }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $date }}</td>
                            <td class="px-4 py-2 text-right text-gray-600 dark:text-gray-400">{{ $size }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400">
            {{ __('app.settings.backup.folder') }}: <span class="font-mono">{{ $backupPath }}</span>
        </p>
    </div>
@endif

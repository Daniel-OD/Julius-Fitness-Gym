@props([
    'headings' => [],
])

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
        @if (count($headings))
            <thead>
                <tr>
                    @foreach ($headings as $heading)
                        <th scope="col"
                            class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ $heading }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            {{ $slot }}
        </tbody>
    </table>
</div>

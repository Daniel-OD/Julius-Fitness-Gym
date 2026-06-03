@props([
    'headings' => [],
])

<div class="-mx-5 overflow-x-auto overscroll-x-contain sm:mx-0">
    <table class="min-w-[36rem] w-full divide-y divide-zinc-200 text-sm dark:divide-white/8">
        @if (count($headings))
            <thead>
                <tr>
                    @foreach ($headings as $heading)
                        <th scope="col"
                            class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-white/40">
                            {{ $heading }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-zinc-100 dark:divide-white/6">
            {{ $slot }}
        </tbody>
    </table>
</div>

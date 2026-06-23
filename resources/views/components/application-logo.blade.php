{{-- Julius Gym — full athletic badge (navy disc · orange ring · arched JULIUS / GYM · bold interlocked JG).
     The arched type uses Archivo, which is loaded in head-meta.blade.php.
     Use for centred / larger placements (auth screens, splash, marketing hero):
     <x-application-logo class="h-24 w-24" /> --}}
<svg viewBox="0 0 200 200" width="80" height="80" role="img" aria-label="Julius Gym" {{ $attributes }}>
    <circle cx="100" cy="100" r="97" fill="#16222e"/>
    <circle cx="100" cy="100" r="92" fill="none" stroke="#ff5a1f" stroke-width="5"/>
    <circle cx="100" cy="100" r="83" fill="none" stroke="#ff5a1f" stroke-width="1.4" opacity="0.55"/>

    <defs>
        <path id="jg-arc-top" d="M 32 102 A 68 68 0 0 1 168 102"/>
        <path id="jg-arc-bot" d="M 39 100 A 61 61 0 0 0 161 100"/>
    </defs>
    <text fill="#f2e6cb" font-family="Archivo, sans-serif" font-weight="800" font-size="19.5" letter-spacing="6.5">
        <textPath href="#jg-arc-top" startOffset="50%" text-anchor="middle">JULIUS</textPath>
    </text>
    <text fill="#f2e6cb" font-family="Archivo, sans-serif" font-weight="800" font-size="19.5" letter-spacing="8.5">
        <textPath href="#jg-arc-bot" startOffset="50%" text-anchor="middle">GYM</textPath>
    </text>

    {{-- bold interlocked JG: orange J woven over cream G --}}
    <text x="115" y="124" text-anchor="middle" fill="#f2e6cb" font-family="Archivo, sans-serif" font-weight="900" font-size="72" letter-spacing="-3">G</text>
    <text x="85" y="124" text-anchor="middle" fill="#16222e" stroke="#16222e" stroke-width="7.9" stroke-linejoin="round" font-family="Archivo, sans-serif" font-weight="900" font-size="72">J</text>
    <text x="85" y="124" text-anchor="middle" fill="#ff5a1f" font-family="Archivo, sans-serif" font-weight="900" font-size="72">J</text>
</svg>

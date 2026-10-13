@props(['name', 'class' => ''])

<svg {{ $attributes->class(['ui-icon', 'ui-icon-'.$name, $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/>
            @break
        @case('building')
            <path class="icon-fill" d="M5 21V5.5A1.5 1.5 0 0 1 6.5 4h9A1.5 1.5 0 0 1 17 5.5V21H5Zm13 0V11h1.5a1.5 1.5 0 0 1 1.5 1.5V21h-3Z"/>
            <path d="M5 21V5.5A1.5 1.5 0 0 1 6.5 4h9A1.5 1.5 0 0 1 17 5.5V21M3 21h18M8 8h1m3 0h1M8 12h1m3 0h1M8 16h1m3 0h1m5-2h1m-1 4h1"/>
            <path d="M8.5 21v-2.5h3V21"/>
            @break
        @case('users')
            <circle cx="9" cy="8" r="3.5"/>
            <path d="M3 20v-1.5a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5V20M16 4.8a3.5 3.5 0 0 1 0 6.4m2 2.5a4.5 4.5 0 0 1 3 4.3v1"/>
            @break
        @case('box')
        @case('package')
            <path d="m12 3 9 4.8v8.4L12 21l-9-4.8V7.8L12 3Z"/>
            <path d="m3.5 7.5 8.5 4.6 8.5-4.6M12 12.1V21m-4-16.9 9 4.7"/>
            @break
        @case('briefcase')
            <rect x="3" y="7" width="18" height="14" rx="2"/>
            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18m-11 0v2h4v-2"/>
            @break
        @case('construction')
            <path class="icon-fill" d="M5 14a7 7 0 0 1 14 0H5Z"/>
            <path d="M3 15h18M5 15v2a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2M12 5v6m-5.2-3.8 1.4 1.4m9.6-1.4-1.4 1.4"/>
            @break
        @case('bolt')
            <path d="M13 2 4.5 13H11l-1 9 9.5-12H13l1-8Z"/>
            @break
        @case('faucet')
            <path d="M5 10h14v3H5zM7 10V7a2 2 0 0 1 2-2h4a3 3 0 0 1 3 3v2m-6-5V3m-2 0h4m-7 10v2a3 3 0 0 0 3 3h1"/>
            <path d="M16 13v3m0 0a2 2 0 1 0 4 0"/>
            @break
        @case('paintbrush')
            <path class="icon-fill" d="m14.5 3 6.5 6.5-4.2 4.2-6.5-6.5L14.5 3Z"/>
            <path d="m14.5 3 6.5 6.5-4.2 4.2-6.5-6.5L14.5 3Zm-4.2 4.2-6.5 6.5a3.5 3.5 0 0 0 5 5l6.5-6.5M6 18c-1 .3-1.7 1-2 2 1.6-.2 2.7.1 3.4 1 .7-.8 1.6-1.1 2.6-1"/>
            @break
        @case('chat')
        @case('messages')
            <path class="icon-fill" d="M12 5a7 7 0 0 1 7 7 7 7 0 0 1-7 7 7.7 7.7 0 0 1-3.6-.8L4 20l1.3-4.2A7.1 7.1 0 0 1 5 12a7 7 0 0 1 7-7Z"/>
            <path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8 8 0 0 1-3.6-.8L4 20l1.3-4.2a7.2 7.2 0 0 1-1.3-4.3A7.5 7.5 0 0 1 12 4h.5a7.5 7.5 0 0 1 7.5 7.5Z"/>
            <path d="M8 10h8m-8 4h5"/>
            @break
        @case('globe')
            <circle cx="12" cy="12" r="9"/>
            <path d="M3 12h18m-9-9a14 14 0 0 1 0 18m0-18a14 14 0 0 0 0 18"/>
            @break
        @case('grid')
        @case('dashboard')
            <rect class="icon-fill" x="3" y="3" width="8" height="8" rx="2"/>
            <rect class="icon-fill" x="13" y="3" width="8" height="5" rx="2"/>
            <rect class="icon-fill" x="13" y="10" width="8" height="11" rx="2"/>
            <rect class="icon-fill" x="3" y="13" width="8" height="8" rx="2"/>
            <rect x="3" y="3" width="8" height="8" rx="2"/>
            <rect x="13" y="3" width="8" height="5" rx="2"/>
            <rect x="13" y="10" width="8" height="11" rx="2"/>
            <rect x="3" y="13" width="8" height="8" rx="2"/>
            @break
        @case('user')
            <circle class="icon-fill" cx="12" cy="8" r="3.2"/>
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 21a8 8 0 0 1 16 0"/>
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5m5 5H3m9-9h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>
            @break
        @case('settings')
            <path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/>
            <path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.5-.9l-1.7.6-1.4-2.4 1.4-1.1a7 7 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.6a8 8 0 0 1 1.5-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.6 1.4 2.4-1.4 1.1a7 7 0 0 1 0 1.7Z"/>
            @break
        @case('shopping-bag')
            <path class="icon-fill" d="M5.5 9h13l.8 11H4.7l.8-11Z"/>
            <path d="M5 8h14l1 13H4L5 8Z"/>
            <path d="M9 9V6a3 3 0 0 1 6 0v3"/>
            @break
        @case('bell')
            <path class="icon-fill" d="M12 4a5.5 5.5 0 0 0-5.5 5.5c0 5.5-1.5 7-2.5 8.5h16c-1-1.5-2.5-3-2.5-8.5A5.5 5.5 0 0 0 12 4Z"/>
            <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12a2 2 0 0 0 4 0"/>
            @break
        @case('camera')
            <path d="M4 7h3l2-3h6l2 3h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z"/>
            <circle cx="12" cy="13" r="3.5"/>
            @break
        @case('check')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('shield')
            <path class="icon-fill" d="M12 4 6 6v5c0 4.8 2.6 8.1 6 10 3.4-1.9 6-5.2 6-10V6l-6-2Z"/>
            <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
            <path d="m9 12 2 2 4-4"/>
            @break
        @case('pin')
            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
            <circle cx="12" cy="10" r="2.5"/>
            @break
        @case('trending')
            <path d="M3 17 9 11l4 4 8-8m-6 0h6v6"/>
            @break
        @case('book')
            <path d="M12 7v14m0-14C9 4.5 5.5 4 3 5v14c2.5-1 6-.5 9 2m0-14c3-2.5 6.5-3 9-2v14c-2.5-1-6-.5-9 2"/>
            @break
        @case('arrow')
            <path d="M5 12h14m-6-6 6 6-6 6"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/>
            <path d="M8 3v4m8-4v4M3 10h18"/>
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16"/>
            @break
        @case('close')
            <path d="M6 6l12 12M18 6 6 18"/>
            @break
        @case('phone')
            <path d="M5 4h4l2 5-2.5 1.5a12 12 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/>
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="m4 7 8 6 8-6"/>
            @break
        @case('facebook')
            <path d="M14 8h2.5V4.5H14A4.5 4.5 0 0 0 9.5 9v2.5H7V15h2.5v6H13v-6h2.5l.5-3.5h-3V9a1 1 0 0 1 1-1Z"/>
            @break
        @case('instagram')
            <rect x="4" y="4" width="16" height="16" rx="5"/>
            <circle cx="12" cy="12" r="3.5"/>
            <circle class="icon-fill" cx="17" cy="7" r="1.4"/>
            @break
        @case('x')
            <path d="M5 4h4.5l4 5.5L18.5 4H21l-6.8 8.3L21.5 20h-4.5l-4.4-6-5.1 6H4.5l7.3-8.9L5 4Z"/>
            @break
        @case('linkedin')
            <rect x="3" y="3" width="18" height="18" rx="3"/>
            <path d="M8 10.5V17m0-7v.01M12 17v-3.8a1.8 1.8 0 0 1 3.6 0V17m-7.6-9v.01M12 10.5V11"/>
            @break
        @case('youtube')
            <rect x="2.5" y="6" width="19" height="12" rx="4"/>
            <path class="icon-fill" d="m10.5 9.8 4.5 2.2-4.5 2.2V9.8Z"/>
            <path d="m10.5 9.8 4.5 2.2-4.5 2.2V9.8Z"/>
            @break
        @case('whatsapp')
            <path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3Z"/>
            <path d="M9 9.2a3.2 3.2 0 0 1 5.7 2c-.3 1.7-2.2 3.4-3.9 3.9-1 .3-2.3.1-3.1-.7l-.6 1.8 1.7-.6c-.8-.8-1-2.1-.7-3.1.5-1.7 2-3.6 3.9-3.9l-3 3.6Z"/>
            @break
        @default
            <circle cx="12" cy="12" r="8"/>
            <path d="M12 8v4m0 4h.01"/>
    @endswitch
</svg>

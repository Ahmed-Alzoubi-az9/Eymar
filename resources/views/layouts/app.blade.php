<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#4a0d17">
        <title>@yield('title', __('messages.brand')) — Eymar</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/eymar-burgundy.css') }}">
    </head>
    <body class="app-shell">
        <a class="skip-link" href="#main">{{ __('messages.home') }}</a>
        <header class="site-header">
            <div class="topbar">
                <div class="container topbar-inner">
                    <div class="topbar-contact">
                        <span class="topbar-item"><x-icon name="phone" /> <span dir="ltr">+962 7 9000 0000</span></span>
                        <a class="topbar-item" href="mailto:hello@eymar.jo"><x-icon name="mail" /> hello@eymar.jo</a>
                        <span class="topbar-item topbar-location"><x-icon name="pin" /> Amman, Jordan</span>
                    </div>
                    <div class="topbar-social">
                        <a class="social-btn" href="#" aria-label="Eymar on Facebook" title="Facebook"><x-icon name="facebook" /></a>
                        <a class="social-btn" href="#" aria-label="Eymar on Instagram" title="Instagram"><x-icon name="instagram" /></a>
                        <a class="social-btn" href="#" aria-label="Eymar on X" title="X"><x-icon name="x" /></a>
                        <a class="social-btn" href="#" aria-label="Eymar on LinkedIn" title="LinkedIn"><x-icon name="linkedin" /></a>
                        <a class="social-btn" href="#" aria-label="Eymar on YouTube" title="YouTube"><x-icon name="youtube" /></a>
                        <a class="social-btn" href="#" aria-label="Eymar on WhatsApp" title="WhatsApp"><x-icon name="whatsapp" /></a>
                    </div>
                </div>
            </div>
            <div class="container header-inner">
                <a class="brand" href="{{ route('home') }}">
                    <x-icon name="building" class="brand-mark" />
                    <span class="brand-text"><span class="brand-word">{{ __('messages.brand') }}</span><small class="brand-tag">Build · Rent · Hire</small></span>
                </a>
                <button class="nav-toggle" type="button" aria-label="Menu" aria-expanded="false" data-nav-toggle>
                    <x-icon name="menu" class="icon-open" />
                    <x-icon name="close" class="icon-close" />
                </button>
                <nav class="main-nav" aria-label="{{ __('messages.home') }}" data-main-nav>
                    <div class="nav-links">
                        <a class="nav-link" href="{{ route('professionals.index') }}" @if (request()->routeIs('professionals.*')) aria-current="page" @endif><x-icon name="users" /><span>{{ __('messages.professionals') }}</span></a>
                        <a class="nav-link" href="{{ route('products.index') }}" @if (request()->routeIs('products.*')) aria-current="page" @endif><x-icon name="box" /><span>{{ __('messages.products') }}</span></a>
                        <a class="nav-link" href="{{ route('projects.index') }}" @if (request()->routeIs('projects.*')) aria-current="page" @endif><x-icon name="briefcase" /><span>{{ __('messages.projects') }}</span></a>
                        <a class="nav-link" href="{{ route('community.index') }}" @if (request()->routeIs('community.*')) aria-current="page" @endif><x-icon name="chat" /><span>{{ __('messages.community') }}</span></a>
                    </div>
                </nav>
                <div class="header-actions">
                    <a class="language-toggle" href="{{ route('locale.update', app()->isLocale('ar') ? 'en' : 'ar') }}" aria-label="{{ __('messages.language') }}">
                        <x-icon name="globe" />
                        {{ __('messages.language') }}
                    </a>
                    @auth
                        <a class="icon-link" href="{{ route('dashboard') }}" aria-label="{{ __('messages.dashboard') }}" title="{{ __('messages.dashboard') }}"><x-icon name="dashboard" /></a>
                        <a class="icon-link" href="{{ route('messages.index') }}" aria-label="Messages" title="Messages"><x-icon name="chat" />@if (auth()->user()->unreadMessagesCount() > 0)<span class="badge">{{ auth()->user()->unreadMessagesCount() }}</span>@endif</a>
                        <a class="icon-link" href="{{ route('bookings.index') }}" aria-label="Appointments" title="Appointments"><x-icon name="briefcase" /></a>
                        @if (auth()->user()->isServiceProvider())
                            <a class="icon-link" href="{{ route('schedules.index') }}" aria-label="Schedule" title="Schedule"><x-icon name="calendar" /></a>
                            <a class="icon-link" href="{{ route('portfolio.index') }}" aria-label="Portfolio" title="Portfolio"><x-icon name="package" /></a>
                        @endif
                        <a class="icon-link" href="{{ route('profile.edit') }}" aria-label="{{ __('messages.edit_profile') }}" title="{{ __('messages.edit_profile') }}"><x-icon name="user" /></a>
                        <a class="icon-link" href="{{ route('cart.index') }}" aria-label="{{ __('messages.cart') }}" title="{{ __('messages.cart') }}"><x-icon name="shopping-bag" /></a>
                        <a class="icon-link" href="{{ route('notifications.index') }}" aria-label="{{ __('messages.notifications') }}" title="{{ __('messages.notifications') }}"><x-icon name="bell" /></a>
                        <form class="logout-form" action="{{ route('logout') }}" method="post" onsubmit="return confirm('{{ __('messages.confirm_logout') }}')">
                            @csrf
                            <button class="icon-link" type="submit" aria-label="{{ __('messages.logout') }}" title="{{ __('messages.logout') }}"><x-icon name="logout" /></button>
                        </form>
                    @else
                        <a class="button button-outline header-auth-link" href="{{ route('login') }}">{{ __('messages.login') }}</a>
                        <a class="button button-dark header-auth-link" href="{{ route('register') }}">{{ __('messages.register') }}</a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="container app-page" id="main">
            @if (session('success'))
                <div class="notice notice-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="notice notice-error" role="alert">
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="site-footer">
            <div class="container footer-grid">
                <div class="footer-brand">
                    <a class="brand brand-footer" href="{{ route('home') }}">
                        <x-icon name="building" class="brand-mark" />
                        <span class="brand-text"><span class="brand-word">Eymar</span><small class="brand-tag">إعمار · Jordan</small></span>
                    </a>
                    <p class="footer-about">One marketplace for construction materials, equipment, machinery and trusted professionals across Jordan.</p>
                    <div class="footer-social">
                        <a class="social-btn social-btn-lg" href="#" aria-label="Eymar on Facebook" title="Facebook"><x-icon name="facebook" /></a>
                        <a class="social-btn social-btn-lg" href="#" aria-label="Eymar on Instagram" title="Instagram"><x-icon name="instagram" /></a>
                        <a class="social-btn social-btn-lg" href="#" aria-label="Eymar on X" title="X"><x-icon name="x" /></a>
                        <a class="social-btn social-btn-lg" href="#" aria-label="Eymar on LinkedIn" title="LinkedIn"><x-icon name="linkedin" /></a>
                        <a class="social-btn social-btn-lg" href="#" aria-label="Eymar on YouTube" title="YouTube"><x-icon name="youtube" /></a>
                        <a class="social-btn social-btn-lg" href="#" aria-label="Eymar on WhatsApp" title="WhatsApp"><x-icon name="whatsapp" /></a>
                    </div>
                </div>
                <nav class="footer-col" aria-label="Explore">
                    <h3>Explore</h3>
                    <a href="{{ route('professionals.index') }}">{{ __('messages.professionals') }}</a>
                    <a href="{{ route('products.index') }}">{{ __('messages.products') }}</a>
                    <a href="{{ route('projects.index') }}">{{ __('messages.projects') }}</a>
                    <a href="{{ route('community.index') }}">{{ __('messages.community') }}</a>
                </nav>
                <nav class="footer-col" aria-label="Account">
                    <h3>Account</h3>
                    <a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
                    <a href="{{ route('orders.index') }}">{{ __('messages.orders') }}</a>
                    <a href="{{ route('cart.index') }}">{{ __('messages.cart') }}</a>
                    <a href="{{ route('profile.edit') }}">{{ __('messages.edit_profile') }}</a>
                </nav>
                <div class="footer-col" aria-label="Contact">
                    <h3>Contact</h3>
                    <span class="footer-contact"><x-icon name="pin" /> Amman, Jordan</span>
                    <a class="footer-contact" href="mailto:hello@eymar.jo"><x-icon name="mail" /> hello@eymar.jo</a>
                    <span class="footer-contact"><x-icon name="phone" /> <span dir="ltr">+962 7 9000 0000</span></span>
                </div>
            </div>
            <div class="container footer-bottom">
                <p class="footer-note">© {{ now()->year }} <strong>Eymar</strong> — Better work, built together.</p>
                <div class="footer-links">
                    <a href="{{ route('professionals.index') }}">{{ __('messages.professionals') }}</a>
                    <a href="{{ route('products.index') }}">{{ __('messages.products') }}</a>
                    <a href="{{ route('community.index') }}">{{ __('messages.community') }}</a>
                </div>
            </div>
        </footer>
        <script>
        (function () {
            var btn = document.querySelector('[data-nav-toggle]');
            var nav = document.querySelector('[data-main-nav]');
            if (!btn || !nav) return;
            btn.addEventListener('click', function () {
                var open = document.body.classList.toggle('nav-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            nav.addEventListener('click', function (e) {
                if (e.target.closest('a')) document.body.classList.remove('nav-open');
            });
        })();
        </script>
    </body>
</html>

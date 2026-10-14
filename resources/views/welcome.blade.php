<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#4a0d17">
        <meta
            name="description"
            content="{{ app()->isLocale('ar') ? 'إيمار يربطك بالحرفيين والشركات المناسبة لإنجاز مشاريع البناء والصيانة والتحسين.' : 'Find skilled professionals and trusted businesses for repairs, construction, and projects of every size.' }}"
        >
        <title>{{ app()->isLocale('ar') ? 'إيمار — اعثر على الأشخاص المناسبين لمشروعك' : 'Eymar — Find the right people for your next project' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/eymar-burgundy.css') }}">
    </head>
    <body>
        <a class="skip-link" href="#main" data-en="Skip to content" data-ar="انتقل إلى المحتوى">Skip to content</a>

        <header class="site-header">
            <div class="container header-inner">
                <a
                    class="brand"
                    href="#home"
                    data-aria-en="Eymar home"
                    data-aria-ar="الصفحة الرئيسية لإيمار"
                    aria-label="Eymar home"
                >
                    <x-icon name="building" class="brand-mark" />
                    <span class="brand-text"><span class="brand-word" data-en="Eymar" data-ar="إعمار">Eymar</span><small class="brand-tag" data-en="Build · Rent · Hire" data-ar="ابنِ · استأجر · وظِّف">Build · Rent · Hire</small></span>
                </a>

                <button class="nav-toggle" type="button" aria-label="Menu" aria-expanded="false" data-nav-toggle>
                    <x-icon name="menu" class="icon-open" />
                    <x-icon name="close" class="icon-close" />
                </button>
                <nav class="main-nav" aria-label="Main navigation" data-aria-en="Main navigation" data-aria-ar="التنقل الرئيسي" data-main-nav>
                    <div class="nav-links">
                        <a class="nav-link" href="{{ route('professionals.index') }}"><x-icon name="users" /><span data-en="Professionals" data-ar="المحترفون">Professionals</span></a>
                        <a class="nav-link" href="{{ route('products.index') }}"><x-icon name="box" /><span data-en="Products" data-ar="المنتجات">Products</span></a>
                        <a class="nav-link" href="{{ route('projects.index') }}"><x-icon name="briefcase" /><span data-en="Projects" data-ar="المشاريع">Projects</span></a>
                        <a class="nav-link" href="{{ route('community.index') }}"><x-icon name="chat" /><span data-en="Community" data-ar="المجتمع">Community</span></a>
                    </div>
                </nav>

                <div class="header-actions">
                    <a
                        class="language-toggle"
                        href="{{ route('locale.update', app()->isLocale('ar') ? 'en' : 'ar') }}"
                        data-language-toggle
                        data-aria-en="Switch language to Arabic"
                        data-aria-ar="التبديل إلى الإنجليزية"
                        aria-label="Switch language to Arabic"
                    >
                        <x-icon name="globe" class="language-icon" />
                        <span data-language-toggle-label>العربية</span>
                    </a>
                    @auth
                        <a class="icon-link" href="{{ route('dashboard') }}" aria-label="{{ __('messages.dashboard') }}" title="{{ __('messages.dashboard') }}"><x-icon name="grid" /></a>
                        <a class="icon-link" href="{{ route('profile.edit') }}" aria-label="{{ __('messages.edit_profile') }}" title="{{ __('messages.edit_profile') }}"><x-icon name="user" /></a>
                        <a class="icon-link" href="{{ route('cart.index') }}" aria-label="{{ __('messages.cart') }}" title="{{ __('messages.cart') }}"><x-icon name="shopping-bag" /></a>
                        <a class="icon-link" href="{{ route('notifications.index') }}" aria-label="{{ __('messages.notifications') }}" title="{{ __('messages.notifications') }}"><x-icon name="bell" /></a>
                        <form class="logout-form" action="{{ route('logout') }}" method="post" onsubmit="return confirm('{{ __('messages.confirm_logout') }}')">
                            @csrf
                            <button class="icon-link" type="submit" aria-label="{{ __('messages.logout') }}" title="{{ __('messages.logout') }}"><x-icon name="logout" /></button>
                        </form>
                    @else
                        <a class="button button-outline header-auth-link" href="{{ route('login') }}" data-en="Sign in" data-ar="تسجيل الدخول">Sign in</a>
                        <a class="button button-dark header-auth-link" href="{{ route('register') }}" data-en="Join Eymar" data-ar="انضم إلى إيمار">Join Eymar</a>
                    @endauth
                </div>
            </div>
        </header>

        <main id="main">
            <section class="hero" id="home">
                <div class="container hero-grid">
                    <div class="hero-copy">
                        <p class="eyebrow">
                            <span class="eyebrow-dot" aria-hidden="true"></span>
                            <span data-en="A better way to build, fix &amp; improve" data-ar="طريقة أفضل للبناء والإصلاح والتطوير">
                                A better way to build, fix &amp; improve
                            </span>
                        </p>
                        <h1 data-en="Great work starts with the right people." data-ar="كل مشروع ناجح يبدأ بالأشخاص المناسبين.">
                            Great work starts with the right people.
                        </h1>
                        <p class="hero-description" data-en="From quick repairs to major projects, find skilled professionals and trusted businesses to help you make it happen." data-ar="من الإصلاحات السريعة إلى المشاريع الكبيرة، اعثر على الحرفيين والشركات المناسبة لمساعدتك على إنجاز ما تحتاج إليه.">
                            From quick repairs to major projects, find skilled professionals and trusted businesses to help you make it happen.
                        </p>
                        <div class="hero-actions">
                            <a class="button button-dark" href="{{ route('professionals.index') }}">
                                <span data-en="Explore professionals" data-ar="اكتشف المحترفين">Explore professionals</span>
                                <x-icon name="arrow" class="button-arrow" />
                            </a>
                            <a class="button button-outline" href="{{ route('register') }}" data-en="I’m a professional" data-ar="أنا صاحب مهنة">
                                I’m a professional
                            </a>
                        </div>
                        <p class="hero-note">
                            <x-icon name="shield" class="note-icon" />
                            <span data-en="A welcoming place for customers, companies and skilled hands." data-ar="مساحة تجمع العملاء والشركات وأصحاب المهارات.">
                                A welcoming place for customers, companies and skilled hands.
                            </span>
                        </p>
                    </div>

                    <div class="hero-art" aria-hidden="true">
                        <div class="art-backdrop"></div>
                        <span class="art-label" data-en="MAKE ROOM FOR WHAT’S NEXT" data-ar="مساحة لما هو قادم">
                            MAKE ROOM FOR WHAT’S NEXT
                        </span>
                        <img class="construction-image" src="{{ asset('images/construction-illustration.png') }}" alt="">
                        <div class="project-card">
                            <span class="project-card-label" data-en="YOUR NEXT PROJECT" data-ar="مشروعك القادم">YOUR NEXT PROJECT</span>
                            <strong data-en="A good plan brings the right people together." data-ar="الخطة الجيدة تجمع الأشخاص المناسبين.">
                                A good plan brings the right people together.
                            </strong>
                            <div class="project-tags">
                                <span data-en="Plan" data-ar="خطط">Plan</span>
                                <span data-en="Find" data-ar="اعثر">Find</span>
                                <span data-en="Build" data-ar="أنجز">Build</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="trust-strip" aria-label="What you can find on Eymar" data-aria-en="What you can find on Eymar" data-aria-ar="ما ستجده على إيمار">
                <div class="container trust-grid">
                    <div class="trust-item">
                        <span class="trust-icon" aria-hidden="true">
                            <x-icon name="building" />
                        </span>
                        <div>
                            <strong data-en="One place" data-ar="مكان واحد">One place</strong>
                            <span data-en="For projects big and small" data-ar="للمشاريع الصغيرة والكبيرة">For projects big and small</span>
                        </div>
                    </div>
                    <div class="trust-item">
                        <span class="trust-icon" aria-hidden="true">
                            <x-icon name="pin" />
                        </span>
                        <div>
                            <strong data-en="Local know-how" data-ar="خبرات من مجتمعك">Local know-how</strong>
                            <span data-en="Find people who understand your area" data-ar="تواصل مع من يعرفون منطقتك">Find people who understand your area</span>
                        </div>
                    </div>
                    <div class="trust-item">
                        <span class="trust-icon" aria-hidden="true">
                            <x-icon name="trending" />
                        </span>
                        <div>
                            <strong data-en="Room to grow" data-ar="فرص للنمو">Room to grow</strong>
                            <span data-en="For customers and professionals" data-ar="للعملاء وأصحاب المهن">For customers and professionals</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section services" id="services">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">
                            <span class="eyebrow-dot" aria-hidden="true"></span>
                            <span data-en="Find your kind of help" data-ar="اعثر على الخدمة التي تحتاجها">Find your kind of help</span>
                        </p>
                        <h2 data-en="A place for every kind of project." data-ar="مكان لكل أنواع المشاريع.">
                            A place for every kind of project.
                        </h2>
                        <p data-en="Explore the work people need and the skills professionals bring to the table." data-ar="اكتشف الخدمات التي يحتاجها الناس والمهارات التي يقدمها المحترفون.">
                            Explore the work people need and the skills professionals bring to the table.
                        </p>
                    </div>

                    <div class="services-grid">
                        <a class="service-card" href="#how-it-works" data-aria-en="Learn about construction services" data-aria-ar="تعرّف على خدمات الإنشاءات">
                            <span class="service-icon" aria-hidden="true">
                                <x-icon name="construction" />
                            </span>
                            <span class="service-card-label">
                                <span data-en="Construction" data-ar="الإنشاءات">Construction</span>
                                <x-icon name="arrow" class="service-arrow" />
                            </span>
                        </a>
                        <a class="service-card" href="#how-it-works" data-aria-en="Learn about home repair services" data-aria-ar="تعرّف على خدمات إصلاح المنزل">
                            <span class="service-icon" aria-hidden="true">
                                <x-icon name="home" />
                            </span>
                            <span class="service-card-label">
                                <span data-en="Home repairs" data-ar="إصلاحات المنزل">Home repairs</span>
                                <x-icon name="arrow" class="service-arrow" />
                            </span>
                        </a>
                        <a class="service-card" href="#how-it-works" data-aria-en="Learn about electrical services" data-aria-ar="تعرّف على خدمات الكهرباء">
                            <span class="service-icon" aria-hidden="true">
                                <x-icon name="bolt" />
                            </span>
                            <span class="service-card-label">
                                <span data-en="Electrical" data-ar="الكهرباء">Electrical</span>
                                <x-icon name="arrow" class="service-arrow" />
                            </span>
                        </a>
                        <a class="service-card" href="#how-it-works" data-aria-en="Learn about plumbing services" data-aria-ar="تعرّف على خدمات السباكة">
                            <span class="service-icon" aria-hidden="true">
                                <x-icon name="faucet" />
                            </span>
                            <span class="service-card-label">
                                <span data-en="Plumbing" data-ar="السباكة">Plumbing</span>
                                <x-icon name="arrow" class="service-arrow" />
                            </span>
                        </a>
                        <a class="service-card" href="#how-it-works" data-aria-en="Learn about interior finishing services" data-aria-ar="تعرّف على خدمات التشطيبات الداخلية">
                            <span class="service-icon" aria-hidden="true">
                                <x-icon name="paintbrush" />
                            </span>
                            <span class="service-card-label">
                                <span data-en="Interior finishing" data-ar="التشطيبات الداخلية">Interior finishing</span>
                                <x-icon name="arrow" class="service-arrow" />
                            </span>
                        </a>
                        <a class="service-card" href="#how-it-works" data-aria-en="Learn about business services" data-aria-ar="تعرّف على خدمات الشركات">
                            <span class="service-icon" aria-hidden="true">
                                <x-icon name="building" />
                            </span>
                            <span class="service-card-label">
                                <span data-en="Business services" data-ar="خدمات الشركات">Business services</span>
                                <x-icon name="arrow" class="service-arrow" />
                            </span>
                        </a>
                    </div>
                </div>
            </section>

            <section class="section how-it-works" id="how-it-works">
                <div class="container">
                    <div class="section-heading">
                        <p class="eyebrow">
                            <span class="eyebrow-dot" aria-hidden="true"></span>
                            <span data-en="A clearer way forward" data-ar="خطوات أوضح لمشروعك">A clearer way forward</span>
                        </p>
                        <h2 data-en="From your idea to a job well done." data-ar="من فكرتك إلى إنجاز متقن.">
                            From your idea to a job well done.
                        </h2>
                        <p data-en="Eymar is designed to make finding the right help feel simple, clear and human." data-ar="صُممت إيمار لتجعل العثور على المساعدة المناسبة أمراً بسيطاً وواضحاً.">
                            Eymar is designed to make finding the right help feel simple, clear and human.
                        </p>
                    </div>

                    <div class="steps-grid">
                        <article class="step-card">
                            <span class="step-number">01</span>
                            <h3 data-en="Share what you need" data-ar="اشرح ما تحتاج إليه">Share what you need</h3>
                            <p data-en="Start with the project, repair or service you have in mind." data-ar="ابدأ بالمشروع أو الإصلاح أو الخدمة التي تفكر فيها.">
                                Start with the project, repair or service you have in mind.
                            </p>
                        </article>
                        <article class="step-card">
                            <span class="step-number">02</span>
                            <h3 data-en="Find your match" data-ar="اعثر على الشخص المناسب">Find your match</h3>
                            <p data-en="Explore skilled professionals and businesses that fit the work." data-ar="اكتشف الحرفيين والشركات المناسبة لطبيعة العمل.">
                                Explore skilled professionals and businesses that fit the work.
                            </p>
                        </article>
                        <article class="step-card">
                            <span class="step-number">03</span>
                            <h3 data-en="Move your project forward" data-ar="تقدّم بمشروعك">Move your project forward</h3>
                            <p data-en="Connect, plan the next step and bring your idea to life." data-ar="تواصل وخطط للخطوة التالية وحوّل فكرتك إلى واقع.">
                                Connect, plan the next step and bring your idea to life.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="professional-cta" id="for-professionals">
                <div class="container">
                    <div class="professional-panel">
                        <div class="professional-copy">
                            <p class="eyebrow">
                                <span class="eyebrow-dot" aria-hidden="true"></span>
                                <span data-en="For the people who make it happen" data-ar="للأيدي التي تصنع الفرق">
                                    For the people who make it happen
                                </span>
                            </p>
                            <h2 data-en="Your skills can open new doors." data-ar="مهاراتك تفتح لك أبواباً جديدة.">
                                Your skills can open new doors.
                            </h2>
                            <p data-en="Showcase what you do and connect with customers and companies looking for skilled people." data-ar="اعرض مهاراتك وتواصل مع العملاء والشركات الباحثة عن أصحاب الخبرة.">
                                Showcase what you do and connect with customers and companies looking for skilled people.
                            </p>
                        </div>
                        <a class="button button-light professional-action" href="#how-it-works">
                            <span data-en="See how Eymar works" data-ar="اكتشف كيف تعمل إيمار">See how Eymar works</span>
                            <x-icon name="arrow" class="button-arrow" />
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="container footer-grid">
                <div class="footer-brand">
                    <a class="brand brand-footer" href="#home">
                        <x-icon name="building" class="brand-mark" />
                        <span class="brand-text"><span>eymar</span><small class="brand-tag">إعمار · Jordan</small></span>
                    </a>
                    <p class="footer-about" data-en="One marketplace for construction materials, equipment, machinery and trusted professionals across Jordan." data-ar="سوق واحد لمواد البناء والمعدات والآليات والحرفيين الموثوقين في الأردن.">One marketplace for construction materials, equipment, machinery and trusted professionals across Jordan.</p>
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
                    <h3 data-en="Explore" data-ar="اكتشف">Explore</h3>
                    <a href="{{ route('professionals.index') }}" data-en="Professionals" data-ar="المحترفون">Professionals</a>
                    <a href="{{ route('products.index') }}" data-en="Products" data-ar="المنتجات">Products</a>
                    <a href="{{ route('community.index') }}" data-en="Community" data-ar="المجتمع">Community</a>
                </nav>
                <nav class="footer-col" aria-label="Company">
                    <h3 data-en="Company" data-ar="الشركة">Company</h3>
                    <a href="#services" data-en="Services" data-ar="الخدمات">Services</a>
                    <a href="#how-it-works" data-en="How it works" data-ar="كيف يعمل">How it works</a>
                    <a href="#for-professionals" data-en="For professionals" data-ar="لأصحاب المهن">For professionals</a>
                </nav>
                <div class="footer-col" aria-label="Contact">
                    <h3 data-en="Contact" data-ar="تواصل">Contact</h3>
                    <span class="footer-contact"><x-icon name="pin" /> Amman, Jordan</span>
                    <a class="footer-contact" href="mailto:hello@eymar.jo"><x-icon name="mail" /> hello@eymar.jo</a>
                    <span class="footer-contact"><x-icon name="phone" /> <span dir="ltr">+962 7 9000 0000</span></span>
                </div>
            </div>
            <div class="container footer-bottom">
                <p class="footer-note">
                    <strong>Eymar</strong>
                    <span data-en=" — Better work, built together." data-ar=" — إنجاز أفضل، نصنعه معاً.">
                        — Better work, built together.
                    </span>
                    <span>© {{ now()->year }}</span>
                </p>
                <nav class="footer-links" aria-label="Footer navigation" data-aria-en="Footer navigation" data-aria-ar="روابط التذييل">
                    <a href="#services" data-en="Services" data-ar="الخدمات">Services</a>
                    <a href="#how-it-works" data-en="How it works" data-ar="كيف يعمل">How it works</a>
                    <a href="#for-professionals" data-en="For professionals" data-ar="لأصحاب المهن">For professionals</a>
                </nav>
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

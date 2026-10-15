import './bootstrap';

const languageToggle = document.querySelector('[data-language-toggle]');
const languageLabel = document.querySelector('[data-language-toggle-label]');

function setLanguage(language) {
    const isArabic = language === 'ar';

    document.documentElement.lang = isArabic ? 'ar' : 'en';
    document.documentElement.dir = isArabic ? 'rtl' : 'ltr';
    document.title = isArabic
        ? 'إعمار — اعثر على الأشخاص المناسبين لمشروعك'
        : 'Eymar — Find the right people for your next project';

    document.querySelectorAll('[data-en][data-ar]').forEach((element) => {
        element.textContent = isArabic ? element.dataset.ar : element.dataset.en;
    });

    document.querySelectorAll('[data-aria-en][data-aria-ar]').forEach((element) => {
        element.setAttribute('aria-label', isArabic ? element.dataset.ariaAr : element.dataset.ariaEn);
    });

    const description = document.querySelector('meta[name="description"]');

    if (description) {
        description.content = isArabic
            ? 'إعمار يربطك بالحرفيين والشركات المناسبة لإنجاز مشاريع البناء والصيانة والتحسين.'
            : 'Find skilled professionals and trusted businesses for repairs, construction, and projects of every size.';
    }

    if (languageLabel) {
        languageLabel.textContent = isArabic ? 'English' : 'العربية';
    }

    if (languageToggle) {
        languageToggle.setAttribute('aria-label', isArabic ? 'Switch language to English' : 'Switch language to Arabic');
    }
}

languageToggle?.addEventListener('click', () => {
    setLanguage(document.documentElement.lang === 'ar' ? 'en' : 'ar');
});

setLanguage(document.documentElement.lang);

const accountRoleSelect = document.querySelector('[data-account-role]');
const companyFields = document.querySelector('[data-company-fields]');

function updateCompanyFields() {
    if (!accountRoleSelect || !companyFields) {
        return;
    }

    const isCompany = accountRoleSelect.value === 'company';
    companyFields.hidden = !isCompany;
    companyFields.querySelectorAll('[data-company-required]').forEach((input) => {
        input.required = isCompany;
    });
}

accountRoleSelect?.addEventListener('change', updateCompanyFields);
updateCompanyFields();

const productTypeSelect = document.querySelector('[data-product-type-select]');

function updateProductFields() {
    if (!productTypeSelect) {
        return;
    }

    document.querySelectorAll('[data-product-fields]').forEach((fieldset) => {
        const isSelected = fieldset.dataset.productFields.split(',').includes(productTypeSelect.value);
        fieldset.hidden = !isSelected;
    });

    document.querySelectorAll('[data-required-product-type]').forEach((input) => {
        input.required = input.dataset.requiredProductType.split(',').includes(productTypeSelect.value);
    });
}

productTypeSelect?.addEventListener('change', updateProductFields);
updateProductFields();

const fulfillmentSelect = document.querySelector('[data-fulfillment-select]');
const rentalDates = document.querySelector('[data-rental-dates]');

function updateRentalDates() {
    if (!fulfillmentSelect || !rentalDates) {
        return;
    }

    const isRental = fulfillmentSelect.value === 'rental';
    rentalDates.hidden = !isRental;
    rentalDates.querySelectorAll('[data-rental-required]').forEach((input) => {
        input.required = isRental;
    });
}

fulfillmentSelect?.addEventListener('change', updateRentalDates);
updateRentalDates();

const rentableToggle = document.querySelector('[data-rentable-toggle]');
const rentalRateField = document.querySelector('[data-rental-rate-field]');

function updateRentalRateField() {
    if (!rentableToggle || !rentalRateField) {
        return;
    }

    rentalRateField.hidden = !rentableToggle.checked;
    const input = rentalRateField.querySelector('input');
    if (input) {
        input.required = rentableToggle.checked;
    }
}

rentableToggle?.addEventListener('change', updateRentalRateField);
productTypeSelect?.addEventListener('change', () => {
    if (productTypeSelect.value === 'material' && rentableToggle) {
        rentableToggle.checked = false;
    }
    updateRentalRateField();
});
updateRentalRateField();

const providerTypeSelect = document.querySelector('[data-provider-type]');
const providerFields = document.querySelector('[data-provider-fields]');

function updateProviderFields() {
    if (!providerTypeSelect || !providerFields) {
        return;
    }

    const isProvider = providerTypeSelect.value !== '';
    providerFields.hidden = !isProvider;
    providerFields.querySelectorAll('[data-provider-required]').forEach((input) => {
        input.required = isProvider;
    });
}

providerTypeSelect?.addEventListener('change', updateProviderFields);
updateProviderFields();

document.querySelectorAll('[data-profile-image-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            return;
        }

        const previewUrl = URL.createObjectURL(file);
        document.querySelectorAll('[data-profile-avatar]').forEach((image) => {
            image.src = previewUrl;
            image.hidden = false;
        });
        document.querySelectorAll('[data-profile-avatar-fallback]').forEach((fallback) => {
            fallback.hidden = true;
        });
    });
});

const companyLogoInput = document.querySelector('[data-company-logo-input]');
const companyLogoPreview = document.querySelector('[data-company-logo-preview]');

companyLogoInput?.addEventListener('change', () => {
    const file = companyLogoInput.files?.[0];
    if (!file || !companyLogoPreview) {
        return;
    }

    const image = document.createElement('img');
    image.src = URL.createObjectURL(file);
    image.alt = '';
    companyLogoPreview.replaceChildren(image);
});

if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const motionTargets = [
        '.hero-copy',
        '.hero-art',
        '.trust-item',
        '.section-heading',
        '.service-card',
        '.step-card',
        '.professional-panel',
        '.page-heading',
        '.profile-page-heading',
        '.stat-card',
        '.action-card',
        '.listing-card',
        '.profile-section',
        '.feed-post',
        '.table-wrap',
        '.form-card',
        '.order-summary',
        '.notification-card',
    ].join(',');
    const revealTargets = document.querySelectorAll(motionTargets);

    if ('IntersectionObserver' in window && revealTargets.length > 0) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -24px 0px' });

        revealTargets.forEach((target) => {
            target.dataset.motionReveal = '';
            // Above-the-fold content is already visible: show it immediately
            // instead of waiting for the observer callback (removes reload flash/delay).
            const rect = target.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                target.classList.add('is-visible');
            } else {
                revealObserver.observe(target);
            }
        });
        document.body.classList.add('motion-ready');
        // Safety net: never leave content hidden if the observer is slow.
        setTimeout(() => {
            document.querySelectorAll('[data-motion-reveal]:not(.is-visible)').forEach((target) => {
                target.classList.add('is-visible');
            });
        }, 800);
    }
}

(function () {
    'use strict';

    var openState = null;
    var menuSequence = 0;

    function selectedOption(select) {
        return select.selectedIndex > -1 ? select.options[select.selectedIndex] : null;
    }

    function optionLabel(option) {
        return option ? option.textContent.replace(/\s+/g, ' ').trim() : '';
    }

    function updateDisplay(select) {
        var shell = select.closest('.select-shell');

        if (!shell) {
            return;
        }

        var value = shell.querySelector('.select-display__value');
        var option = selectedOption(select);
        var label = optionLabel(option);

        if (value) {
            value.textContent = label;
        }

        shell.classList.toggle('is-placeholder', !option || option.disabled || option.value === '');
    }

    function closeMenu(returnFocus) {
        if (!openState) {
            return;
        }

        var state = openState;
        openState = null;

        state.menu.remove();
        state.shell.classList.remove('is-open');
        state.select.setAttribute('aria-expanded', 'false');
        state.select.removeAttribute('aria-controls');

        if (returnFocus) {
            state.select.focus({ preventScroll: true });
        }
    }

    function positionMenu(menu, shell) {
        var rect = shell.getBoundingClientRect();
        var viewportGap = 12;
        var width = Math.max(rect.width, 180);
        width = Math.min(width, window.innerWidth - viewportGap * 2);

        var left = Math.min(Math.max(viewportGap, rect.left), window.innerWidth - width - viewportGap);

        menu.style.width = width + 'px';
        menu.style.left = left + 'px';

        var menuHeight = menu.offsetHeight;
        var top = rect.bottom + 8;

        if (top + menuHeight > window.innerHeight - viewportGap && rect.top > menuHeight + viewportGap) {
            top = rect.top - menuHeight - 8;
        }

        menu.style.top = Math.max(viewportGap, top) + 'px';
    }

    function chooseOption(select, index) {
        var option = select.options[index];

        if (!option || option.disabled) {
            return;
        }

        var changed = select.selectedIndex !== index;
        select.selectedIndex = index;
        updateDisplay(select);

        if (changed) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        closeMenu(true);
    }

    function openMenu(select) {
        closeMenu(false);

        if (select.disabled) {
            return;
        }

        var shell = select.closest('.select-shell');

        if (!shell) {
            return;
        }

        var menu = document.createElement('div');
        menu.className = 'select-menu';
        menu.id = 'select-menu-' + (++menuSequence);
        menu.setAttribute('role', 'listbox');

        var label = select.getAttribute('aria-label') || select.closest('label')?.textContent.replace(/\s+/g, ' ').trim() || '';
        if (label) {
            menu.setAttribute('aria-label', label);
        }

        Array.prototype.forEach.call(select.options, function (option, index) {
            if (option.hidden) {
                return;
            }

            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'select-option';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', index === select.selectedIndex ? 'true' : 'false');
            item.disabled = option.disabled;

            var text = document.createElement('span');
            text.className = 'select-option__label';
            text.textContent = optionLabel(option);
            item.appendChild(text);
            item.title = text.textContent;

            item.addEventListener('click', function () {
                chooseOption(select, index);
            });

            menu.appendChild(item);
        });

        menu.style.visibility = 'hidden';
        document.body.appendChild(menu);
        positionMenu(menu, shell);
        menu.style.visibility = '';

        var selected = menu.querySelector('[aria-selected="true"]');
        if (selected) {
            menu.scrollTop = Math.max(0, selected.offsetTop - menu.clientHeight / 2 + selected.offsetHeight / 2);
        }

        shell.classList.add('is-open');
        select.setAttribute('aria-expanded', 'true');
        select.setAttribute('aria-controls', menu.id);
        openState = { menu: menu, shell: shell, select: select };
    }

    function toggleMenu(select) {
        if (openState && openState.select === select) {
            closeMenu(false);
            return;
        }

        openMenu(select);
    }

    function enhanceSelect(select) {
        if (select.multiple || select.size > 1 || select.dataset.nativeSelect !== undefined) {
            return;
        }

        if (select.closest('.select-shell')) {
            return;
        }

        var shell = document.createElement('div');
        shell.className = 'select-shell';

        var display = document.createElement('span');
        display.className = 'select-display';
        display.setAttribute('aria-hidden', 'true');

        var value = document.createElement('span');
        value.className = 'select-display__value';
        display.appendChild(value);

        select.parentNode.insertBefore(shell, select);
        shell.appendChild(display);
        shell.appendChild(select);
        select.classList.add('select-control');

        updateDisplay(select);
        shell.classList.toggle('is-disabled', select.disabled);

        select.addEventListener('pointerdown', function (event) {
            if (select.disabled || event.button !== 0) {
                return;
            }

            event.preventDefault();
            select.focus({ preventScroll: true });
            toggleMenu(select);
        });

        select.addEventListener('change', function () {
            updateDisplay(select);
        });

        var form = select.form;
        if (form && form.dataset.selectResetBound !== 'true') {
            form.dataset.selectResetBound = 'true';
            form.addEventListener('reset', function () {
                window.setTimeout(function () {
                    form.querySelectorAll('select.select-control').forEach(updateDisplay);
                }, 0);
            });
        }
    }

    document.addEventListener('pointerdown', function (event) {
        if (!openState) {
            return;
        }

        if (event.target.closest?.('.select-menu') || event.target.closest?.('.select-shell')) {
            return;
        }

        closeMenu(false);
    });

    document.addEventListener('keydown', function (event) {
        if (!openState) {
            return;
        }

        var keys = ['Escape', 'Tab', 'ArrowDown', 'ArrowUp', 'Enter', ' '];

        if (keys.indexOf(event.key) !== -1) {
            closeMenu(false);
        }
    });

    window.addEventListener('resize', function () {
        closeMenu(false);
    });

    window.addEventListener('scroll', function (event) {
        if (!openState) {
            return;
        }

        if (event.target instanceof Element && event.target.closest?.('.select-menu')) {
            return;
        }

        closeMenu(false);
    }, true);

    function init() {
        document.querySelectorAll('select').forEach(enhanceSelect);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

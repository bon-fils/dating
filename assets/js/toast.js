(function () {
    const STYLES = {
        success: 'bg-emerald-50 border-emerald-200 text-emerald-800',
        error: 'bg-rose-50 border-rose-200 text-rose-800',
        info: 'bg-slate-50 border-slate-200 text-slate-800',
        match: 'bg-gradient-to-r from-pink-500 to-rose-500 border-transparent text-white shadow-lg shadow-pink-500/25',
    };

    const ICONS = {
        success: 'fa-circle-check text-emerald-500',
        error: 'fa-circle-exclamation text-rose-500',
        info: 'fa-circle-info text-slate-500',
        match: 'fa-heart text-white',
    };

    let stack = null;

    function getStack() {
        if (stack) return stack;
        stack = document.createElement('div');
        stack.id = 'matchme-toast-stack';
        stack.className = 'fixed top-20 right-4 z-[100] flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm pointer-events-none sm:right-6';
        stack.setAttribute('aria-live', 'polite');
        stack.setAttribute('aria-atomic', 'true');
        document.body.appendChild(stack);
        return stack;
    }

    function dismiss(toast) {
        toast.classList.add('opacity-0', 'translate-x-4');
        setTimeout(() => toast.remove(), 200);
    }

    window.MatchMeToast = {
        show(message, type = 'info', options = {}) {
            const duration = options.duration ?? (type === 'error' ? 6000 : 4500);
            const toastType = STYLES[type] ? type : 'info';
            const el = document.createElement('div');
            el.className = [
                'pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl border shadow-md',
                'transform transition-all duration-200 opacity-100 translate-x-0',
                STYLES[toastType],
            ].join(' ');

            const icon = document.createElement('i');
            icon.className = `fa-solid ${ICONS[toastType]} mt-0.5 flex-shrink-0`;

            const body = document.createElement('div');
            body.className = 'flex-1 min-w-0';

            const text = document.createElement('p');
            text.className = 'text-xs font-bold leading-snug';
            text.textContent = message;
            body.appendChild(text);

            if (options.actionUrl && options.actionLabel) {
                const link = document.createElement('a');
                link.href = options.actionUrl;
                link.className = 'mt-2 inline-block text-xs font-bold underline opacity-90 hover:opacity-100';
                link.textContent = options.actionLabel;
                body.appendChild(link);
            }

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'toast-close flex-shrink-0 opacity-60 hover:opacity-100 p-0.5';
            closeBtn.setAttribute('aria-label', 'Dismiss');
            closeBtn.innerHTML = '<i class="fa-solid fa-xmark text-xs"></i>';
            closeBtn.addEventListener('click', () => dismiss(el));

            el.append(icon, body, closeBtn);
            getStack().prepend(el);

            if (duration > 0) {
                setTimeout(() => dismiss(el), duration);
            }

            return el;
        },
    };
})();

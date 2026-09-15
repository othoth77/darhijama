import Alpine from 'alpinejs';

// Tracking léger des clics WhatsApp (Dashboard Analytics — voir Modules/Media
// ou futur Modules/Notifications). Envoi asynchrone non bloquant via sendBeacon.
Alpine.data('whatsappCta', (source = 'unknown', context = {}) => ({
    track() {
        const payload = JSON.stringify({ source, ...context });
        if (navigator.sendBeacon) {
            navigator.sendBeacon('/analytics/whatsapp-click', payload);
        }
    },
}));

// Compte à rebours 100% front-end (Module Invitations) — aucune tâche planifiée requise.
Alpine.data('countdown', (targetIso) => ({
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
    interval: null,
    init() {
        this.tick();
        this.interval = setInterval(() => this.tick(), 1000);
    },
    tick() {
        const diff = Math.max(0, new Date(targetIso).getTime() - Date.now());
        this.days = Math.floor(diff / 86400000);
        this.hours = Math.floor((diff % 86400000) / 3600000);
        this.minutes = Math.floor((diff % 3600000) / 60000);
        this.seconds = Math.floor((diff % 60000) / 1000);
    },
    destroy() {
        clearInterval(this.interval);
    },
}));

// Révélation au défilement des vues dar-hijama::public (sans dépendance). Les
// éléments déjà visibles au chargement sont marqués avant d'activer le masquage,
// pour éviter tout clignotement ou décalage du contenu au-dessus de la ligne de flottaison.
const revealTargets = document.querySelectorAll('.dh-reveal');
if (revealTargets.length > 0) {
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -8% 0px' });

        revealTargets.forEach((element) => {
            if (element.getBoundingClientRect().top < window.innerHeight) {
                element.classList.add('is-visible');
            } else {
                observer.observe(element);
            }
        });
        document.documentElement.classList.add('dh-motion');
    }
}

window.Alpine = Alpine;
Alpine.start();

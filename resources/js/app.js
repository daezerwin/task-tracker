document.addEventListener('alpine:init', () => {
    // One shared ticking clock for the live time on the dashboard, rather
    // than a setInterval per element.
    Alpine.store('clock', {
        now: new Date(),

        init() {
            setInterval(() => {
                this.now = new Date();
            }, 1000);
        },
    });
});

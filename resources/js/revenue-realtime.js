/**
 * Live total revenue via Reverb (private user channel).
 * Updates municipality dashboard stat and refreshes Filament admin widgets.
 */
export function initRevenueRealtime() {
    const userId = typeof window !== 'undefined' ? window.__notificationUserId : undefined;
    if (!userId || !window.Echo) {
        return;
    }

    if (window.__revenueRealtimeBound) {
        return;
    }
    window.__revenueRealtimeBound = true;

    const applyRevenue = (payload) => {
        const formatted =
            payload?.formatted_total ??
            (payload?.total_revenue != null
                ? `$${Number(payload.total_revenue).toLocaleString(undefined, {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                  })}`
                : null);

        if (!formatted) {
            return;
        }

        const el = document.getElementById('stat-total-revenue');
        if (el) {
            el.textContent = formatted;
        }

        if (typeof window.Livewire !== 'undefined') {
            window.Livewire.dispatch('revenue-updated', { formatted_total: formatted });
        }
    };

    window.Echo.private(`App.Models.User.${userId}`)
        .listen('.revenue.updated', applyRevenue)
        .error((status) => {
            console.warn('[Revenue] Echo subscription error', status);
        });
}

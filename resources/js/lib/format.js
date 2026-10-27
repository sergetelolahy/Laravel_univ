export function formatDate(value) {
    if (!value) return '—';
    const date = new Date(value.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString('fr-FR');
}

export function formatDateTime(value) {
    if (!value) return '—';
    const date = new Date(value.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' });
}

export function formatNumber(value, decimals = 2) {
    if (value === null || value === undefined || value === '') return '—';
    const n = Number(value);
    if (Number.isNaN(n)) return value;
    return n.toFixed(decimals);
}

export function toDateInputValue(value) {
    if (!value) return '';
    return String(value).slice(0, 10);
}

const VARIANTS = {
    error: 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200',
    success: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
    info: 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200',
    warning: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
};

export default function Alert({ variant = 'info', children, className = '' }) {
    if (!children) return null;

    return <div className={`rounded-md px-4 py-3 text-sm ${VARIANTS[variant]} ${className}`}>{children}</div>;
}

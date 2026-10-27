const VARIANTS = {
    primary: 'bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600 disabled:bg-indigo-300',
    secondary: 'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 disabled:text-slate-400',
    danger: 'bg-rose-600 text-white hover:bg-rose-500 focus-visible:outline-rose-600 disabled:bg-rose-300',
    ghost: 'text-slate-600 hover:bg-slate-100 disabled:text-slate-300',
};

export default function Button({
    variant = 'primary',
    type = 'button',
    disabled = false,
    loading = false,
    className = '',
    children,
    ...rest
}) {
    return (
        <button
            type={type}
            disabled={disabled || loading}
            className={`inline-flex items-center justify-center gap-2 rounded-md px-3.5 py-2 text-sm font-semibold shadow-sm transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed ${VARIANTS[variant]} ${className}`}
            {...rest}
        >
            {loading && (
                <svg className="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
            )}
            {children}
        </button>
    );
}

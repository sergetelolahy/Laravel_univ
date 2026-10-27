export default function Field({ label, required, error, children, hint }) {
    return (
        <label className="block">
            {label && (
                <span className="mb-1 block text-sm font-medium text-slate-700">
                    {label}
                    {required && <span className="text-rose-500"> *</span>}
                </span>
            )}
            {children}
            {hint && !error && <p className="mt-1 text-xs text-slate-500">{hint}</p>}
            {error && <p className="mt-1 text-xs text-rose-600">{error}</p>}
        </label>
    );
}

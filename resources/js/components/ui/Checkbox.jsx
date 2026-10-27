export default function Checkbox({ label, className = '', ...rest }) {
    return (
        <label className={`inline-flex items-center gap-2 text-sm text-slate-700 ${className}`}>
            <input
                type="checkbox"
                className="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600"
                {...rest}
            />
            {label}
        </label>
    );
}

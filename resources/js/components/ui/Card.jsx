export default function Card({ title, actions, children, className = '' }) {
    return (
        <div className={`rounded-lg bg-white shadow-sm ring-1 ring-slate-200 ${className}`}>
            {(title || actions) && (
                <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    {title && <h3 className="text-sm font-semibold text-slate-900">{title}</h3>}
                    {actions && <div className="flex items-center gap-2">{actions}</div>}
                </div>
            )}
            <div className="p-5">{children}</div>
        </div>
    );
}

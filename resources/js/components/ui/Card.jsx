export default function Card({ title, actions, children, className = '' }) {
    return (
        <div className={`rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80 ${className}`}>
            {(title || actions) && (
                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    {title && <h3 className="text-sm font-bold text-slate-900">{title}</h3>}
                    {actions && <div className="flex items-center gap-2">{actions}</div>}
                </div>
            )}
            <div className="p-5">{children}</div>
        </div>
    );
}

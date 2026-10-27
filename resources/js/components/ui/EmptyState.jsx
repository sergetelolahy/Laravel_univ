export default function EmptyState({ title = 'Aucune donnée', description, action }) {
    return (
        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
            <svg className="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={1.5}
                    d="M9 13h6m-6-4h6m2 9H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V18a2 2 0 01-2 2z"
                />
            </svg>
            <p className="text-sm font-medium text-slate-700">{title}</p>
            {description && <p className="text-sm text-slate-500">{description}</p>}
            {action}
        </div>
    );
}

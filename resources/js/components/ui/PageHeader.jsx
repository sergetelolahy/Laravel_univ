export default function PageHeader({ title, description, actions }) {
    return (
        <div className="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 className="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{title}</h1>
                {description && <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{description}</p>}
            </div>
            {actions && <div className="flex items-center gap-2">{actions}</div>}
        </div>
    );
}

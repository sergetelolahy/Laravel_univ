export default function AuthLayout({ children }) {
    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4">
            <div className="w-full max-w-sm">
                <div className="mb-6 flex flex-col items-center gap-2">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">
                        UN
                    </div>
                    <h1 className="text-lg font-semibold text-slate-900">Gestion universitaire</h1>
                </div>
                <div className="rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">{children}</div>
            </div>
        </div>
    );
}

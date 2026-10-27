import { Link } from 'react-router-dom';

export default function NotFoundPage() {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center gap-3 bg-slate-50 px-4 text-center">
            <p className="text-5xl font-bold text-slate-300">404</p>
            <h1 className="text-lg font-semibold text-slate-900">Page introuvable</h1>
            <Link to="/dashboard" className="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Retour au tableau de bord
            </Link>
        </div>
    );
}

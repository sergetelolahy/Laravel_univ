import { useState } from 'react';
import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { ROLE_LABELS, ENSEIGNANT, ETUDIANT } from '../lib/roles';
import { ConfirmDialog } from '../components/ui';

const ROLE_THEME = {
    [ENSEIGNANT]: {
        active: 'bg-indigo-600 text-white shadow-sm shadow-indigo-950/20',
        avatar: 'bg-indigo-100 text-indigo-700',
        accent: 'bg-indigo-500',
    },
    [ETUDIANT]: {
        active: 'bg-emerald-600 text-white shadow-sm shadow-emerald-950/20',
        avatar: 'bg-emerald-100 text-emerald-700',
        accent: 'bg-emerald-500',
    },
};

function initials(user) {
    const prenom = user?.profil?.prenom?.trim()?.[0] ?? '';
    const nom = user?.profil?.nom?.trim()?.[0] ?? '';
    return `${prenom}${nom}`.toUpperCase() || user?.email?.[0]?.toUpperCase() || 'U';
}

export default function AppLayout({ navItems, title }) {
    const { user, role, logout } = useAuth();
    const navigate = useNavigate();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [logoutConfirmOpen, setLogoutConfirmOpen] = useState(false);
    const [loggingOut, setLoggingOut] = useState(false);
    const theme = ROLE_THEME[role] ?? {
        active: 'bg-slate-700 text-white shadow-sm',
        avatar: 'bg-slate-100 text-slate-700',
        accent: 'bg-slate-500',
    };

    async function handleLogout() {
        setLoggingOut(true);
        await logout();
        navigate('/login', { replace: true });
    }

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <div className="flex min-h-screen">
                <aside
                    className={`fixed inset-y-0 left-0 z-40 flex w-[17rem] flex-col border-r border-slate-800 bg-slate-950 text-slate-100 transition-transform duration-200 lg:static lg:translate-x-0 ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <div className="flex h-[4.5rem] shrink-0 items-center gap-3 border-b border-white/10 px-5">
                        <div className="relative flex h-10 w-10 items-center justify-center rounded-xl bg-white text-sm font-black tracking-tight text-slate-950">
                            ENI
                            <span className={`absolute -bottom-1 -right-1 h-3 w-3 rounded-full ring-2 ring-slate-950 ${theme.accent}`} />
                        </div>
                        <div>
                            <p className="text-sm font-bold tracking-wide text-white">Portail universitaire</p>
                            <p className="mt-0.5 text-xs text-slate-400">{ROLE_LABELS[role] ?? role}</p>
                        </div>
                    </div>

                    <div className="px-4 pb-2 pt-6">
                        <p className="px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Espace de travail</p>
                    </div>
                    <nav aria-label="Navigation principale" className="flex flex-1 flex-col gap-1 overflow-y-auto px-3 pb-5">
                        {navItems.map((item) => (
                            <NavLink
                                key={item.to}
                                to={item.to}
                                end={item.end}
                                onClick={() => setSidebarOpen(false)}
                                className={({ isActive }) =>
                                    `flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition-colors ${
                                        isActive ? theme.active : 'text-slate-300 hover:bg-white/8 hover:text-white'
                                    }`
                                }
                            >
                                <span aria-hidden="true" className="h-1.5 w-1.5 rounded-full bg-current opacity-70" />
                                {item.label}
                            </NavLink>
                        ))}
                    </nav>

                    <div className="border-t border-white/10 p-4">
                        <div className="flex items-center gap-3 rounded-xl bg-white/5 p-3">
                            <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold ${theme.avatar}`}>
                                {initials(user)}
                            </div>
                            <div className="min-w-0">
                                <p className="truncate text-sm font-semibold text-white">
                                    {user?.profil?.prenom || user?.profil?.nom
                                        ? `${user?.profil?.prenom ?? ''} ${user?.profil?.nom ?? ''}`.trim()
                                        : user?.email}
                                </p>
                                <p className="truncate text-xs text-slate-400">{user?.email}</p>
                            </div>
                        </div>
                    </div>
                </aside>

                {sidebarOpen && (
                    <button
                        type="button"
                        aria-label="Fermer le menu"
                        className="fixed inset-0 z-30 bg-slate-950/50 backdrop-blur-sm lg:hidden"
                        onClick={() => setSidebarOpen(false)}
                    />
                )}

                <div className="flex min-h-screen min-w-0 flex-1 flex-col">
                    <header className="sticky top-0 z-20 flex h-[4.5rem] items-center justify-between border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur-xl sm:px-7">
                        <div className="flex min-w-0 items-center gap-3">
                            <button
                                type="button"
                                className="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 lg:hidden"
                                onClick={() => setSidebarOpen(true)}
                                aria-label="Ouvrir le menu"
                                aria-expanded={sidebarOpen}
                            >
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <div className={`hidden h-8 w-1 rounded-full sm:block ${theme.accent}`} />
                            <h1 className="truncate text-sm font-semibold text-slate-800 sm:text-base">{title}</h1>
                        </div>
                        <button
                            type="button"
                            onClick={() => setLogoutConfirmOpen(true)}
                            disabled={loggingOut}
                            aria-label={loggingOut ? 'Déconnexion en cours' : 'Déconnexion'}
                            className="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 disabled:cursor-wait disabled:opacity-60"
                        >
                            <svg aria-hidden="true" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 15l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <span className="hidden sm:inline">{loggingOut ? 'Déconnexion…' : 'Déconnexion'}</span>
                        </button>
                    </header>

                    <main className="mx-auto w-full max-w-[1440px] flex-1 px-4 py-6 sm:px-7 sm:py-8 lg:px-10">
                        <Outlet />
                    </main>
                </div>
            </div>
            <ConfirmDialog
                open={logoutConfirmOpen}
                title="Confirmer la déconnexion"
                message="Voulez-vous vraiment vous déconnecter de votre compte ?"
                confirmLabel="Se déconnecter"
                cancelLabel="Rester connecté"
                loading={loggingOut}
                onCancel={() => setLogoutConfirmOpen(false)}
                onConfirm={handleLogout}
            />
        </div>
    );
}

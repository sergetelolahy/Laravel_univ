import { useState } from 'react';
import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { ROLE_LABELS } from '../lib/roles';

export default function AppLayout({ navItems, title }) {
    const { user, logout } = useAuth();
    const navigate = useNavigate();
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [loggingOut, setLoggingOut] = useState(false);

    async function handleLogout() {
        setLoggingOut(true);
        await logout();
        navigate('/login', { replace: true });
    }

    return (
        <div className="min-h-screen bg-slate-50">
            <div className="flex">
                <aside
                    className={`fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-100 transition-transform lg:static lg:translate-x-0 ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <div className="flex h-16 items-center gap-2 px-5">
                        <div className="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-500 text-sm font-bold">
                            UN
                        </div>
                        <span className="text-sm font-semibold">Gestion universitaire</span>
                    </div>
                    <nav className="mt-2 flex flex-col gap-0.5 px-3">
                        {navItems.map((item) => (
                            <NavLink
                                key={item.to}
                                to={item.to}
                                end={item.end}
                                onClick={() => setSidebarOpen(false)}
                                className={({ isActive }) =>
                                    `rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                                        isActive ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                                    }`
                                }
                            >
                                {item.label}
                            </NavLink>
                        ))}
                    </nav>
                </aside>

                {sidebarOpen && (
                    <div className="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" onClick={() => setSidebarOpen(false)} />
                )}

                <div className="flex min-h-screen w-full min-w-0 flex-col">
                    <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white pl-3 pr-4 sm:pr-6">
                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                className="rounded-md p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                                onClick={() => setSidebarOpen(true)}
                                aria-label="Ouvrir le menu"
                            >
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <h1 className="text-sm font-semibold text-slate-900 sm:text-base">{title}</h1>
                        </div>
                        <div className="flex items-center gap-3">
                            <div className="hidden text-right sm:block">
                                <p className="text-sm font-medium text-slate-800">{user?.email}</p>
                                <p className="text-xs text-slate-500">{ROLE_LABELS[user?.role] ?? user?.role}</p>
                            </div>
                            <button
                                type="button"
                                onClick={handleLogout}
                                disabled={loggingOut}
                                className="rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 disabled:opacity-60"
                            >
                                {loggingOut ? 'Déconnexion…' : 'Déconnexion'}
                            </button>
                        </div>
                    </header>

                    <main className="flex-1 py-6 pl-3 pr-4 sm:pr-6">
                        <Outlet />
                    </main>
                </div>
            </div>
        </div>
    );
}

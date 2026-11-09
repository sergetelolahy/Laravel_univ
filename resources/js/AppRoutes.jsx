import { Navigate, Route, Routes } from 'react-router-dom';
import { useAuth } from './context/AuthContext';
import RequireAuth from './components/RequireAuth';
import AppLayout from './layouts/AppLayout';
import { navItemsForRole } from './lib/navigation';
import { ADMIN, SCOLARITE, ENSEIGNANT, ETUDIANT } from './lib/roles';

import LoginPage from './pages/auth/LoginPage';
import ForbiddenPage from './pages/ForbiddenPage';
import NotFoundPage from './pages/NotFoundPage';
import DashboardPage from './pages/DashboardPage';
import FilieresPage from './pages/FilieresPage';
import AnneesPage from './pages/AnneesPage';
import SemestresPage from './pages/SemestresPage';
import CoursListPage from './pages/CoursListPage';
import CoursDetailPage from './pages/CoursDetailPage';
import SallesPage from './pages/SallesPage';
import SeancesPage from './pages/SeancesPage';
import GroupesListPage from './pages/GroupesListPage';
import GroupeDetailPage from './pages/GroupeDetailPage';
import EtudiantsListPage from './pages/EtudiantsListPage';
import EtudiantDetailPage from './pages/EtudiantDetailPage';
import EnseignantsPage from './pages/EnseignantsPage';
import UtilisateursPage from './pages/UtilisateursPage';
import InscriptionsPage from './pages/InscriptionsPage';
import EvaluationsPage from './pages/EvaluationsPage';
import AbsencesPage from './pages/AbsencesPage';
import ValidationSemestrePage from './pages/ValidationSemestrePage';
import MesNotesPage from './pages/etudiant/MesNotesPage';
import MesMoyennesPage from './pages/etudiant/MesMoyennesPage';
import EmploiDuTempsPage from './pages/EmploiDuTempsPage';
import MesInscriptionsPage from './pages/etudiant/MesInscriptionsPage';
import MonProfilPage from './pages/etudiant/MonProfilPage';
import PlanningHebdomadairePage from './pages/PlanningHebdomadairePage';

const ALL_ROLES = [ADMIN, SCOLARITE, ENSEIGNANT, ETUDIANT];

function ProtectedLayout({ roles }) {
    const { role } = useAuth();

    return (
        <RequireAuth roles={roles}>
            <AppLayout navItems={navItemsForRole(role)} title="Gestion universitaire" />
        </RequireAuth>
    );
}

export default function AppRoutes() {
    return (
        <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/403" element={<ForbiddenPage />} />

            <Route element={<ProtectedLayout roles={ALL_ROLES} />}>
                <Route path="/dashboard" element={<DashboardPage />} />
                <Route path="/filieres" element={<FilieresPage />} />
                <Route path="/annees" element={<AnneesPage />} />
                <Route path="/semestres" element={<SemestresPage />} />
                <Route path="/cours" element={<CoursListPage />} />
                <Route path="/cours/:id" element={<CoursDetailPage />} />
                <Route path="/groupes" element={<GroupesListPage />} />
                <Route path="/groupes/:id" element={<GroupeDetailPage />} />
                <Route path="/inscriptions" element={<InscriptionsPage />} />
            </Route>

            <Route element={<ProtectedLayout roles={[ADMIN, SCOLARITE, ENSEIGNANT]} />}>
                <Route path="/edt-hebdomadaire" element={<PlanningHebdomadairePage />} />
                <Route path="/salles" element={<SallesPage />} />
                <Route path="/etudiants" element={<EtudiantsListPage />} />
                <Route path="/etudiants/:id" element={<EtudiantDetailPage />} />
                <Route path="/evaluations" element={<EvaluationsPage />} />
            </Route>

            <Route element={<ProtectedLayout roles={[ADMIN, SCOLARITE]} />}>
                <Route path="/seances" element={<SeancesPage />} />
                <Route path="/enseignants" element={<EnseignantsPage />} />
                <Route path="/utilisateurs" element={<UtilisateursPage />} />
                <Route path="/validation-semestre" element={<ValidationSemestrePage />} />
            </Route>

            <Route element={<ProtectedLayout roles={[ENSEIGNANT, SCOLARITE]} />}>
                <Route path="/absences" element={<AbsencesPage />} />
            </Route>

            <Route element={<ProtectedLayout roles={[ENSEIGNANT, ETUDIANT]} />}>
                <Route path="/mon-espace/emploi-du-temps" element={<EmploiDuTempsPage />} />
            </Route>

            <Route element={<ProtectedLayout roles={[ETUDIANT]} />}>
                <Route path="/mon-espace/notes" element={<MesNotesPage />} />
                <Route path="/mon-espace/moyennes" element={<MesMoyennesPage />} />
                <Route path="/mon-espace/inscriptions" element={<MesInscriptionsPage />} />
                <Route path="/mon-espace/profil" element={<MonProfilPage />} />
            </Route>

            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            <Route path="*" element={<NotFoundPage />} />
        </Routes>
    );
}

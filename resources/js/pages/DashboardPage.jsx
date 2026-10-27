import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useAnneeCourante } from '../features/annees';
import { ROLE_LABELS, ADMIN, SCOLARITE, ENSEIGNANT, ETUDIANT } from '../lib/roles';
import { Card, Badge, PageHeader } from '../components/ui';

const SHORTCUTS = {
    [ADMIN]: [
        { to: '/filieres', label: 'Filières', description: 'Gérer les filières de formation' },
        { to: '/cours', label: 'Cours', description: 'Gérer le catalogue de cours' },
        { to: '/seances', label: 'Emplois du temps', description: 'Planifier les séances' },
        { to: '/utilisateurs', label: 'Comptes utilisateurs', description: 'Créer et gérer les comptes' },
    ],
    [SCOLARITE]: [
        { to: '/etudiants', label: 'Étudiants', description: 'Consulter et mettre à jour les dossiers' },
        { to: '/groupes', label: 'Groupes', description: 'Composer les groupes' },
        { to: '/inscriptions', label: 'Inscriptions', description: 'Inscrire les étudiants aux cours' },
        { to: '/validation-semestre', label: 'Validation de semestre', description: 'Clôturer les semestres' },
    ],
    [ENSEIGNANT]: [
        { to: '/mon-espace/emploi-du-temps', label: 'Mon emploi du temps', description: 'Voir mes séances de la semaine' },
        { to: '/evaluations', label: 'Évaluations & notes', description: 'Créer des évaluations et saisir les notes' },
        { to: '/absences', label: 'Absences', description: "Déclarer les absences d'une séance" },
        { to: '/cours', label: 'Mes cours', description: 'Consulter les cours et les inscrits' },
    ],
    [ETUDIANT]: [
        { to: '/mon-espace/notes', label: 'Mes notes', description: 'Consulter mes notes par cours' },
        { to: '/mon-espace/moyennes', label: 'Mes moyennes', description: 'Suivre mes moyennes par cours et semestre' },
        { to: '/mon-espace/emploi-du-temps', label: 'Mon emploi du temps', description: 'Voir mes séances de la semaine' },
        { to: '/mon-espace/inscriptions', label: "M'inscrire à un cours", description: "Consulter et gérer mes inscriptions" },
    ],
};

export default function DashboardPage() {
    const { user, role } = useAuth();
    const { data: annee } = useAnneeCourante({ enabled: role !== ETUDIANT || true });
    const shortcuts = SHORTCUTS[role] ?? [];

    return (
        <div>
            <PageHeader
                title={`Bonjour${user?.profil?.prenom ? ', ' + user.profil.prenom : ''}`}
                description={user?.email}
                actions={<Badge variant="indigo">{ROLE_LABELS[role] ?? role}</Badge>}
            />

            {annee?.data && (
                <Card className="mb-6">
                    <p className="text-sm text-slate-500">Année universitaire courante</p>
                    <p className="text-lg font-semibold text-slate-900">{annee.data.libelle}</p>
                </Card>
            )}

            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {shortcuts.map((item) => (
                    <Link
                        key={item.to}
                        to={item.to}
                        className="rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200 transition-shadow hover:shadow-md"
                    >
                        <p className="text-sm font-semibold text-slate-900">{item.label}</p>
                        <p className="mt-1 text-xs text-slate-500">{item.description}</p>
                    </Link>
                ))}
            </div>
        </div>
    );
}

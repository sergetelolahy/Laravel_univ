import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useAnneeCourante } from '../features/annees';
import { useCoursList } from '../features/cours';
import { useEvaluations } from '../features/evaluations';
import { useMesInscriptions, useMonEmploiDuTemps } from '../features/etudiants';
import { useMesMoyennes, useMesNotes } from '../features/etudiantConsultation';
import { useMonEmploiDuTempsEnseignant } from '../features/enseignants';
import { formatDate } from '../lib/format';
import { ROLE_LABELS, ADMIN, SCOLARITE, ENSEIGNANT, ETUDIANT } from '../lib/roles';
import { Alert, Badge, Card, PageHeader, Spinner } from '../components/ui';
import { extractErrorMessage } from '../lib/api';

const SHORTCUTS = {
    [ADMIN]: [
        { to: '/filieres', label: 'Filières', description: 'Gérer les filières de formation', icon: '⌘' },
        { to: '/cours', label: 'Cours', description: 'Gérer le catalogue de cours', icon: '▤' },
        { to: '/seances', label: 'Emplois du temps', description: 'Planifier les séances', icon: '◷' },
        { to: '/edt-hebdomadaire', label: 'EDT hebdomadaires', description: 'Publier les plannings validés', icon: '✓' },
        { to: '/utilisateurs', label: 'Comptes utilisateurs', description: 'Créer et gérer les comptes', icon: '◎' },
    ],
    [SCOLARITE]: [
        { to: '/etudiants', label: 'Étudiants', description: 'Consulter et mettre à jour les dossiers', icon: '◎' },
        { to: '/groupes', label: 'Groupes', description: 'Composer les groupes', icon: '◉' },
        { to: '/inscriptions', label: 'Inscriptions', description: 'Inscrire les étudiants aux cours', icon: '▤' },
        { to: '/validation-semestre', label: 'Validation de semestre', description: 'Clôturer les semestres', icon: '✓' },
        { to: '/edt-hebdomadaire', label: 'EDT hebdomadaires', description: 'Préparer et valider les horaires', icon: '◷' },
    ],
    [ENSEIGNANT]: [
        { to: '/edt-hebdomadaire', label: 'Mes disponibilités', description: 'Déclarer mes créneaux libres', icon: '◷' },
        { to: '/mon-espace/emploi-du-temps', label: 'Mon emploi du temps', description: 'Voir mes séances de la semaine', icon: '◷' },
        { to: '/evaluations', label: 'Évaluations & notes', description: 'Créer une évaluation et saisir les notes', icon: '▤' },
        { to: '/absences', label: 'Absences', description: "Déclarer les absences d'une séance", icon: '◎' },
        { to: '/cours', label: 'Mes cours', description: 'Consulter les cours et les inscrits', icon: '⌘' },
    ],
    [ETUDIANT]: [
        { to: '/mon-espace/notes', label: 'Mes notes', description: 'Consulter mes résultats', icon: '▤' },
        { to: '/mon-espace/moyennes', label: 'Mes moyennes', description: 'Suivre ma progression', icon: '↗' },
        { to: '/mon-espace/emploi-du-temps', label: 'Mon emploi du temps', description: 'Voir mes séances planifiées', icon: '◷' },
        { to: '/mon-espace/inscriptions', label: 'Mes inscriptions', description: 'Consulter mes cours', icon: '◎' },
    ],
};

const THEMES = {
    [ENSEIGNANT]: {
        eyebrow: 'ESPACE ENSEIGNANT',
        gradient: 'from-indigo-950 via-indigo-900 to-violet-900',
        icon: 'text-indigo-700 bg-indigo-50',
    },
    [ETUDIANT]: {
        eyebrow: 'ESPACE ÉTUDIANT',
        gradient: 'from-emerald-950 via-emerald-900 to-teal-900',
        icon: 'text-emerald-700 bg-emerald-50',
    },
};

function StatCard({ label, value, caption, icon, theme }) {
    return (
        <Card className="h-full transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-medium text-slate-500">{label}</p>
                    <p className="mt-3 text-3xl font-bold tracking-tight text-slate-950">{value}</p>
                    <p className="mt-1 text-xs text-slate-500">{caption}</p>
                </div>
                <span className={`flex h-11 w-11 items-center justify-center rounded-2xl text-lg font-bold ${theme.icon}`} aria-hidden="true">
                    {icon}
                </span>
            </div>
        </Card>
    );
}

function ShortcutGrid({ shortcuts, theme }) {
    return (
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            {shortcuts.map((item) => (
                <Link
                    key={item.to}
                    to={item.to}
                    className="group rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                >
                    <div className="flex items-center justify-between">
                        <span className={`flex h-10 w-10 items-center justify-center rounded-xl text-lg font-semibold ${theme?.icon ?? 'bg-slate-100 text-slate-700'}`}>
                            {item.icon}
                        </span>
                        <span aria-hidden="true" className="text-slate-300 transition group-hover:translate-x-1 group-hover:text-slate-600">→</span>
                    </div>
                    <p className="mt-5 text-sm font-bold text-slate-900">{item.label}</p>
                    <p className="mt-1 text-sm leading-5 text-slate-500">{item.description}</p>
                </Link>
            ))}
        </div>
    );
}

function SeanceList({ jours, isLoading, error, role }) {
    const seances = (jours ?? []).flatMap((jour) =>
        (jour.seances ?? []).map((seance) => ({ ...seance, date: jour.date }))
    );

    return (
        <Card
            title="Séances de la semaine"
            actions={
                <Link to="/mon-espace/emploi-du-temps" className="text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                    Tout voir <span aria-hidden="true">→</span>
                </Link>
            }
        >
            {isLoading && <Spinner label="Chargement des séances…" />}
            {error && <Alert variant="error">{extractErrorMessage(error, "Impossible de charger l'emploi du temps.")}</Alert>}
            {!isLoading && !error && seances.length === 0 && (
                <p className="py-5 text-center text-sm text-slate-500">Aucune séance prévue cette semaine.</p>
            )}
            {!isLoading && !error && seances.length > 0 && (
                <ul className="divide-y divide-slate-100">
                    {seances.slice(0, 4).map((seance) => (
                        <li key={seance.id} className="flex flex-wrap items-center gap-x-4 gap-y-2 py-3 first:pt-0 last:pb-0">
                            <div className="min-w-16 text-center">
                                <p className="text-xs font-semibold uppercase text-slate-400">{formatDate(seance.date)}</p>
                                <p className="mt-1 text-sm font-bold tabular-nums text-slate-900">{seance.debut.slice(11, 16)}</p>
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-semibold text-slate-900">
                                    {seance.cours?.intitule ?? seance.cours?.code ?? 'Cours'}
                                </p>
                                <p className="mt-1 text-xs text-slate-500">
                                    {role === ENSEIGNANT ? seance.groupe?.nom : `${seance.enseignant?.prenom ?? ''} ${seance.enseignant?.nom ?? ''}`.trim()}
                                    {seance.salle?.nom ? ` · Salle ${seance.salle.nom}` : ''}
                                </p>
                            </div>
                            <Badge variant={role === ENSEIGNANT ? 'indigo' : 'green'}>{seance.fin.slice(11, 16)}</Badge>
                        </li>
                    ))}
                </ul>
            )}
        </Card>
    );
}

export default function DashboardPage() {
    const { user, role } = useAuth();
    const theme = THEMES[role];
    const shortcuts = SHORTCUTS[role] ?? [];
    const { data: annee, isLoading: anneeLoading, error: anneeError } = useAnneeCourante({ enabled: !!role });
    const studentEnabled = role === ETUDIANT;
    const teacherEnabled = role === ENSEIGNANT;

    const notesQuery = useMesNotes({ enabled: studentEnabled });
    const moyennesQuery = useMesMoyennes({ enabled: studentEnabled });
    const inscriptionsQuery = useMesInscriptions({ enabled: studentEnabled });
    const studentSchedule = useMonEmploiDuTemps({}, { enabled: studentEnabled });
    const teacherCourses = useCoursList({ per_page: 100 }, { enabled: teacherEnabled });
    const teacherEvaluations = useEvaluations({ per_page: 100 }, { enabled: teacherEnabled });
    const teacherSchedule = useMonEmploiDuTempsEnseignant({}, { enabled: teacherEnabled });

    const prenom = user?.profil?.prenom?.trim();
    if (teacherEnabled || studentEnabled) {
        const isStudent = studentEnabled;
        const dataQueries = isStudent
            ? [notesQuery, moyennesQuery, inscriptionsQuery]
            : [teacherCourses, teacherEvaluations];
        const queryErrors = dataQueries.filter((query) => query.error);
        const scheduleQuery = isStudent ? studentSchedule : teacherSchedule;
        const jours = scheduleQuery.data?.data?.jours ?? [];
        const inscriptions = inscriptionsQuery.data?.data ?? [];
        const notes = notesQuery.data?.data ?? [];
        const noteCount = notes.reduce((total, cours) => total + (cours.evaluations?.length ?? 0), 0);
        const moyennes = moyennesQuery.data?.moyennes_semestres ?? [];
        const latestAverage = moyennes.length ? moyennes[moyennes.length - 1].moyenne : null;
        const evaluations = teacherEvaluations.data?.data?.data ?? [];
        const courses = teacherCourses.data?.data ?? [];
        const displayedCount = (query, value) => (query.isLoading ? '…' : query.isError ? '—' : value);
        const stats = isStudent
            ? [
                  { label: 'Cours inscrits', value: displayedCount(inscriptionsQuery, inscriptions.length), caption: 'Pour l’année en cours', icon: '▤' },
                  { label: 'Notes publiées', value: displayedCount(notesQuery, noteCount), caption: 'Résultats disponibles', icon: '✓' },
                  {
                      label: 'Moyenne récente',
                      value: moyennesQuery.isLoading
                          ? '…'
                          : moyennesQuery.isError || latestAverage === null
                            ? '—'
                            : `${Number(latestAverage).toFixed(2)}/20`,
                      caption: 'Dernier semestre renseigné',
                      icon: '↗',
                  },
              ]
            : [
                  { label: 'Cours accessibles', value: displayedCount(teacherCourses, courses.length), caption: 'Cours associés à votre compte', icon: '▤' },
                  { label: 'Évaluations', value: displayedCount(teacherEvaluations, evaluations.length), caption: 'Évaluations consultables', icon: '✓' },
                  {
                      label: 'Séances cette semaine',
                      value: displayedCount(
                          scheduleQuery,
                          jours.reduce((total, jour) => total + (jour.seances?.length ?? 0), 0)
                      ),
                      caption: 'Selon l’emploi du temps',
                      icon: '◷',
                  },
              ];

        return (
            <div className="space-y-7">
                <section className={`relative isolate overflow-hidden rounded-3xl bg-gradient-to-br ${theme.gradient} px-6 py-7 text-white shadow-xl shadow-slate-900/10 sm:px-9 sm:py-9`}>
                    <div aria-hidden="true" className="absolute -right-8 -top-20 h-64 w-64 rounded-full border-[36px] border-white/5" />
                    <div aria-hidden="true" className="absolute -bottom-28 right-32 h-64 w-64 rounded-full bg-white/5 blur-2xl" />
                    <div className="relative flex flex-wrap items-end justify-between gap-6">
                        <div>
                            <p className="text-xs font-bold tracking-[0.2em] text-white/65">{theme.eyebrow}</p>
                            <h2 className="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">
                                Bonjour{prenom ? `, ${prenom}` : ''}
                            </h2>
                            <p className="mt-2 max-w-xl text-sm leading-6 text-white/75">
                                {isStudent
                                    ? 'Retrouvez vos cours, vos résultats et les prochaines séances depuis votre espace personnel.'
                                    : 'Retrouvez vos cours, vos évaluations et votre planning depuis votre espace de travail.'}
                            </p>
                        </div>
                        <div className="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                            <p className="text-xs text-white/65">Année universitaire</p>
                            <p className="mt-1 text-lg font-semibold">
                                {anneeLoading ? 'Chargement…' : anneeError ? 'Indisponible' : annee?.data?.libelle ?? 'Non définie'}
                            </p>
                        </div>
                    </div>
                </section>

                {queryErrors.length > 0 && (
                    <Alert variant="error">
                        Certaines informations du tableau de bord n’ont pas pu être chargées. Recharge la page ou ouvre la rubrique concernée pour réessayer.
                    </Alert>
                )}

                <div className="grid gap-4 md:grid-cols-3">
                    {stats.map((stat) => (
                        <StatCard key={stat.label} {...stat} theme={theme} />
                    ))}
                </div>

                <div className="grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
                    <SeanceList jours={jours} isLoading={scheduleQuery.isLoading} error={scheduleQuery.error} role={role} />
                    <div className="space-y-5">
                        <Card title={isStudent ? 'À consulter' : 'Actions rapides'}>
                            <div className="space-y-2">
                                {shortcuts.slice(0, 3).map((item) => (
                                    <Link
                                        key={item.to}
                                        to={item.to}
                                        className="group flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 transition hover:border-slate-200 hover:bg-slate-50"
                                    >
                                        <span>
                                            <span className="block text-sm font-semibold text-slate-800">{item.label}</span>
                                            <span className="mt-0.5 block text-xs text-slate-500">{item.description}</span>
                                        </span>
                                        <span aria-hidden="true" className="text-slate-300 transition group-hover:translate-x-1 group-hover:text-slate-700">→</span>
                                    </Link>
                                ))}
                            </div>
                        </Card>
                        {isStudent && (
                            <Card className="border-emerald-100 bg-emerald-50/60">
                                <p className="text-sm font-semibold text-emerald-950">Votre dossier</p>
                                <p className="mt-1 text-sm leading-5 text-emerald-900/75">
                                    Consultez votre profil ou vos inscriptions pour vérifier les informations enregistrées.
                                </p>
                                <Link to="/mon-espace/profil" className="mt-3 inline-flex text-sm font-bold text-emerald-800 hover:text-emerald-950">
                                    Voir mon profil <span aria-hidden="true" className="ml-1">→</span>
                                </Link>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        );
    }

    const firstName = prenom ? `, ${prenom}` : '';

    return (
        <div>
            <PageHeader
                title={`Bonjour${firstName}`}
                description="Accédez rapidement aux principaux espaces de gestion."
                actions={<Badge variant="indigo">{ROLE_LABELS[role] ?? role}</Badge>}
            />
            {annee?.data && (
                <Card className="mb-6">
                    <p className="text-sm text-slate-500">Année universitaire courante</p>
                    <p className="mt-1 text-lg font-semibold text-slate-900">{annee.data.libelle}</p>
                </Card>
            )}
            <ShortcutGrid shortcuts={shortcuts} theme={null} />
        </div>
    );
}

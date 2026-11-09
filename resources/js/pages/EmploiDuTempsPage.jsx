import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useMonEmploiDuTempsEnseignant } from '../features/enseignants';
import { useMonEmploiDuTemps } from '../features/etudiants';
import { extractErrorMessage } from '../lib/api';
import { Alert, Badge, Card, Field, Input, PageHeader, Spinner } from '../components/ui';

function jourLisible(date) {
    const parsed = new Date(`${date}T12:00:00`);
    return Number.isNaN(parsed.getTime())
        ? date
        : parsed.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
}

export default function EmploiDuTempsPage() {
    const { isEnseignant } = useAuth();
    const [du, setDu] = useState('');
    const [au, setAu] = useState('');
    const params = du && au ? { du, au } : {};

    const enseignantQuery = useMonEmploiDuTempsEnseignant(params, { enabled: isEnseignant });
    const etudiantQuery = useMonEmploiDuTemps(params, { enabled: !isEnseignant });
    const { data, isLoading, error } = isEnseignant ? enseignantQuery : etudiantQuery;

    const jours = data?.data?.jours ?? [];
    const totalSeances = jours.reduce((total, jour) => total + jour.seances.length, 0);

    return (
        <div>
            <PageHeader
                title="Mon emploi du temps"
                description="Organisez votre semaine et retrouvez les informations de chaque séance."
                actions={<Badge variant={isEnseignant ? 'indigo' : 'green'}>{isEnseignant ? 'Enseignant' : 'Étudiant'}</Badge>}
            />

            <Card className="mb-6">
                <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p className="text-sm font-bold text-slate-900">Choisir une période</p>
                        <p className="mt-1 text-sm text-slate-500">
                            Sans dates, le planning affiche automatiquement la semaine en cours.
                        </p>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 lg:w-[26rem]">
                        <Field label="Du">
                            <Input type="date" value={du} onChange={(e) => setDu(e.target.value)} />
                        </Field>
                        <Field label="Au">
                            <Input type="date" value={au} onChange={(e) => setAu(e.target.value)} />
                        </Field>
                    </div>
                </div>
            </Card>

            {!isLoading && !error && (
                <div className="mb-4 flex items-center justify-between">
                    <p className="text-sm font-semibold text-slate-700">Programme de la période</p>
                    <Badge variant="slate">{totalSeances} séance{totalSeances > 1 ? 's' : ''}</Badge>
                </div>
            )}

            {isLoading && <Spinner label="Chargement de l'emploi du temps…" />}
            {error && <Alert variant="error">{extractErrorMessage(error, "Impossible de charger l'emploi du temps.")}</Alert>}

            {!isLoading && !error && totalSeances === 0 && (
                <Card>
                    <div className="py-8 text-center">
                        <span className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-500" aria-hidden="true">
                            ◷
                        </span>
                        <p className="mt-3 font-semibold text-slate-900">Aucune séance planifiée</p>
                        <p className="mt-1 text-sm text-slate-500">Essaie une autre période ou reviens consulter le planning plus tard.</p>
                    </div>
                </Card>
            )}

            <div className="space-y-4">
                {jours
                    .filter((jour) => jour.seances.length > 0)
                    .map((jour) => (
                        <Card
                            key={jour.date}
                            title={<span className="capitalize">{jourLisible(jour.date)}</span>}
                            actions={<Badge variant="slate">{jour.seances.length} séance{jour.seances.length > 1 ? 's' : ''}</Badge>}
                        >
                            <ul className="space-y-3">
                                {jour.seances.map((seance) => (
                                    <li
                                        key={seance.id}
                                        className={`grid gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4 sm:items-center ${
                                            isEnseignant ? 'sm:grid-cols-[6rem_1fr]' : 'sm:grid-cols-[6rem_1fr_auto]'
                                        }`}
                                    >
                                        <div className="flex items-center gap-2 sm:block">
                                            <Badge variant={isEnseignant ? 'indigo' : 'green'}>
                                                {seance.debut.slice(11, 16)}–{seance.fin.slice(11, 16)}
                                            </Badge>
                                        </div>
                                        <div className="min-w-0">
                                            <p className="font-bold text-slate-900">
                                                {seance.cours?.intitule ?? 'Cours sans intitulé'}
                                            </p>
                                            <p className="mt-1 text-sm text-slate-500">
                                                <span className="font-medium text-slate-700">{seance.cours?.code}</span>
                                                {seance.groupe?.nom ? ` · Groupe ${seance.groupe.nom}` : ''}
                                                {seance.salle?.nom ? ` · Salle ${seance.salle.nom}` : ''}
                                            </p>
                                        </div>
                                        {!isEnseignant && (
                                            <div className="text-sm text-slate-600 sm:text-right">
                                                {seance.enseignant?.prenom} {seance.enseignant?.nom}
                                            </div>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </Card>
                    ))}
            </div>
        </div>
    );
}

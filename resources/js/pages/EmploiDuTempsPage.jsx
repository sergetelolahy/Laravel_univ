import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useMonEmploiDuTempsEnseignant } from '../features/enseignants';
import { useMonEmploiDuTemps } from '../features/etudiants';
import { extractErrorMessage } from '../lib/api';
import { Alert, Badge, Card, Field, Input, PageHeader, Spinner } from '../components/ui';

export default function EmploiDuTempsPage() {
    const { isEnseignant } = useAuth();
    const [du, setDu] = useState('');
    const [au, setAu] = useState('');
    const params = du && au ? { du, au } : {};

    const enseignantQuery = useMonEmploiDuTempsEnseignant(params, { enabled: isEnseignant });
    const etudiantQuery = useMonEmploiDuTemps(params, { enabled: !isEnseignant });
    const { data, isLoading, error } = isEnseignant ? enseignantQuery : etudiantQuery;

    const jours = data?.data?.jours ?? [];

    return (
        <div>
            <PageHeader title="Mon emploi du temps" description="Séances planifiées pour la période sélectionnée." />

            <div className="mb-6 flex max-w-md gap-3">
                <Field label="Du">
                    <Input type="date" value={du} onChange={(e) => setDu(e.target.value)} />
                </Field>
                <Field label="Au">
                    <Input type="date" value={au} onChange={(e) => setAu(e.target.value)} />
                </Field>
            </div>

            {isLoading && <Spinner label="Chargement de l'emploi du temps…" />}
            {error && <Alert variant="error">{extractErrorMessage(error, "Impossible de charger l'emploi du temps.")}</Alert>}

            {!isLoading && !error && jours.every((j) => j.seances.length === 0) && (
                <Alert variant="info">Aucune séance planifiée sur cette période.</Alert>
            )}

            <div className="space-y-4">
                {jours
                    .filter((j) => j.seances.length > 0)
                    .map((jour) => (
                        <Card key={jour.date} title={jour.date}>
                            <ul className="space-y-2">
                                {jour.seances.map((s) => (
                                    <li key={s.id} className="flex flex-wrap items-center gap-2 rounded-md bg-slate-50 px-3 py-2 text-sm">
                                        <Badge variant="indigo">
                                            {s.debut.slice(11, 16)}–{s.fin.slice(11, 16)}
                                        </Badge>
                                        <span className="font-medium text-slate-900">{s.cours?.code}</span>
                                        <span className="text-slate-500">{s.cours?.intitule}</span>
                                        <span className="text-slate-500">· {s.groupe?.nom}</span>
                                        <span className="text-slate-500">· {s.salle?.nom}</span>
                                        {!isEnseignant && (
                                            <span className="text-slate-500">
                                                · {s.enseignant?.nom} {s.enseignant?.prenom}
                                            </span>
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

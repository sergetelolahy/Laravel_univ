import { useState } from 'react';
import { useEtudiantMe, useMesInscriptions } from '../../features/etudiants';
import { useCreateInscription } from '../../features/inscriptions';
import { useCoursList } from '../../features/cours';
import { useAnneeCourante } from '../../features/annees';
import { extractErrorMessage } from '../../lib/api';
import { formatDateTime } from '../../lib/format';
import { Alert, Button, Card, DataTable, Field, PageHeader, Select } from '../../components/ui';

export default function MesInscriptionsPage() {
    const { data: meData } = useEtudiantMe();
    const { data, isLoading, error, refetch } = useMesInscriptions();
    const { data: coursData } = useCoursList(
        { filiere_id: meData?.data?.filiere_id, per_page: 100 },
        { enabled: Boolean(meData?.data?.filiere_id) }
    );
    const { data: anneeCouranteData } = useAnneeCourante();
    const createMutation = useCreateInscription();

    const [coursId, setCoursId] = useState('');
    const [formError, setFormError] = useState('');
    const [success, setSuccess] = useState(false);

    const moi = meData?.data;
    const inscriptions = data?.data ?? [];
    const coursListe = coursData?.data ?? [];
    const anneeCourante = anneeCouranteData?.data;

    const coursDejaInscrits = new Set(
        inscriptions
            .filter((inscription) => inscription.annee_id === anneeCourante?.id)
            .map((inscription) => inscription.cours_id)
    );
    const coursDisponibles = coursListe.filter((c) => !coursDejaInscrits.has(c.id));
    const coursParSemestre = coursDisponibles.reduce((groupes, cours) => {
        const semestre = cours.semestre?.numero ?? 'Non défini';
        groupes[semestre] ??= [];
        groupes[semestre].push(cours);
        return groupes;
    }, {});

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setSuccess(false);
        if (!moi || !anneeCourante) return;
        try {
            await createMutation.mutateAsync({
                etudiant_id: moi.id,
                cours_id: Number(coursId),
                annee_id: anneeCourante.id,
            });
            setSuccess(true);
            setCoursId('');
            refetch();
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible de vous inscrire à ce cours."));
        }
    }

    return (
        <div>
            <PageHeader
                title="Mes inscriptions"
                description="Retrouve les cours de ta filière, organisés par semestre, et inscris-toi aux enseignements disponibles."
            />

            <Card title="Ajouter un cours" className="mb-6">
                {!anneeCourante && <Alert variant="warning">Aucune année universitaire courante n'est définie actuellement.</Alert>}
                {moi && !moi.filiere_id && <Alert variant="error">Aucune filière n'est affectée à ton dossier. Contacte la scolarité avant de t'inscrire à un cours.</Alert>}
                {moi?.filiere && (
                    <p className="mb-4 text-sm text-slate-600">
                        Filière : <span className="font-semibold text-slate-900">{moi.filiere.code} — {moi.filiere.nom}</span>
                    </p>
                )}
                <form onSubmit={handleSubmit} className="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div className="flex-1">
                        <Field label="Cours">
                            <Select value={coursId} onChange={(e) => setCoursId(e.target.value)} disabled={!anneeCourante || !moi?.filiere_id}>
                                <option value="">Sélectionner un cours…</option>
                                {Object.entries(coursParSemestre).map(([semestre, coursDuSemestre]) => (
                                    <optgroup key={semestre} label={`Semestre ${semestre}`}>
                                        {coursDuSemestre.map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.code} — {c.intitule}
                                            </option>
                                        ))}
                                    </optgroup>
                                ))}
                            </Select>
                        </Field>
                    </div>
                    <Button type="submit" className="w-full sm:w-auto" loading={createMutation.isPending} disabled={!coursId || !anneeCourante || !moi?.filiere_id}>
                        S'inscrire
                    </Button>
                </form>
                {success && <Alert variant="success" className="mt-3">Inscription réalisée avec succès.</Alert>}
                {formError && <Alert variant="error" className="mt-3">{formError}</Alert>}
            </Card>

            <Card title="Cours suivis" actions={<span className="text-xs font-medium text-slate-500">{inscriptions.length} inscription{inscriptions.length > 1 ? 's' : ''}</span>}>
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={inscriptions}
                    emptyTitle="Aucune inscription pour le moment"
                    columns={[
                        {
                            key: 'cours',
                            header: 'Cours',
                            render: (r) => r.cours
                                ? `${r.cours.code} — ${r.cours.intitule}`
                                : 'Cours indisponible',
                        },
                        { key: 'semestre', header: 'Semestre', render: (r) => coursListe.find((c) => c.id === r.cours_id)?.semestre?.numero ?? '—' },
                        { key: 'date_inscription', header: "Date d'inscription", render: (r) => formatDateTime(r.date_inscription) },
                    ]}
                />
            </Card>
        </div>
    );
}

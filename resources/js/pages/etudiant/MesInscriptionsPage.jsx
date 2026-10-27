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
    const { data: coursData } = useCoursList();
    const { data: anneeCouranteData } = useAnneeCourante();
    const createMutation = useCreateInscription();

    const [coursId, setCoursId] = useState('');
    const [formError, setFormError] = useState('');
    const [success, setSuccess] = useState(false);

    const moi = meData?.data;
    const inscriptions = data?.data ?? [];
    const coursListe = coursData?.data ?? [];
    const anneeCourante = anneeCouranteData?.data;

    const coursDejaInscrits = new Set(inscriptions.map((i) => i.cours_id));
    const coursDisponibles = coursListe.filter((c) => !coursDejaInscrits.has(c.id));

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
            <PageHeader title="Mes inscriptions" description="Mes inscriptions aux cours et auto-inscription." />

            <Card title="M'inscrire à un cours" className="mb-6 max-w-lg">
                {!anneeCourante && <Alert variant="warning">Aucune année universitaire courante n'est définie actuellement.</Alert>}
                <form onSubmit={handleSubmit} className="flex items-end gap-3">
                    <div className="flex-1">
                        <Field label="Cours">
                            <Select value={coursId} onChange={(e) => setCoursId(e.target.value)} disabled={!anneeCourante}>
                                <option value="">Sélectionner un cours…</option>
                                {coursDisponibles.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.code} — {c.intitule}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                    </div>
                    <Button type="submit" loading={createMutation.isPending} disabled={!coursId || !anneeCourante}>
                        S'inscrire
                    </Button>
                </form>
                {success && <Alert variant="success" className="mt-3">Inscription réalisée avec succès.</Alert>}
                {formError && <Alert variant="error" className="mt-3">{formError}</Alert>}
            </Card>

            <Card title="Mes inscriptions actuelles">
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={inscriptions}
                    emptyTitle="Aucune inscription pour le moment"
                    columns={[
                        {
                            key: 'cours',
                            header: 'Cours',
                            render: (r) => coursListe.find((c) => c.id === r.cours_id)?.code ?? `#${r.cours_id}`,
                        },
                        { key: 'date_inscription', header: "Date d'inscription", render: (r) => formatDateTime(r.date_inscription) },
                    ]}
                />
            </Card>
        </div>
    );
}

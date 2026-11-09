import { useState } from 'react';
import { useSemestres } from '../features/semestres';
import { useEtudiants } from '../features/etudiants';
import { useAnnees, useAnneeCourante } from '../features/annees';
import { useValiderSemestre } from '../features/validationSemestre';
import { extractErrorMessage } from '../lib/api';
import { Alert, Badge, Button, Card, Field, PageHeader, Select } from '../components/ui';

export default function ValidationSemestrePage() {
    const { data: semestresData } = useSemestres();
    const { data: etudiantsData, isLoading: etudiantsLoading, error: etudiantsError } = useEtudiants({ per_page: 100 });
    const { data: anneesData } = useAnnees();
    const { data: anneeCouranteData } = useAnneeCourante();
    const semestres = semestresData?.data ?? [];
    const etudiants = etudiantsData?.data ?? [];
    const annees = anneesData?.data ?? [];

    const [semestreId, setSemestreId] = useState('');
    const [etudiantId, setEtudiantId] = useState('');
    const [anneeId, setAnneeId] = useState(anneeCouranteData?.data ? String(anneeCouranteData.data.id) : '');
    const [error, setError] = useState('');
    const [resultat, setResultat] = useState(null);

    const mutation = useValiderSemestre(semestreId);
    const semestreSelectionne = semestres.find((semestre) => String(semestre.id) === semestreId);
    const etudiantsDuProgramme = semestreSelectionne?.filiere?.id
        ? etudiants.filter((etudiant) => etudiant.filiere_id === semestreSelectionne.filiere.id)
        : etudiants;

    function choisirSemestre(value) {
        setSemestreId(value);
        setEtudiantId('');
        setResultat(null);
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setError('');
        setResultat(null);
        try {
            const data = await mutation.mutateAsync({ etudiant_id: Number(etudiantId), annee_id: Number(anneeId) });
            setResultat(data);
        } catch (err) {
            setError(extractErrorMessage(err, 'Impossible de valider ce semestre.'));
        }
    }

    return (
        <div>
            <PageHeader
                title="Validation de semestre"
                description="Calcule la moyenne d'un étudiant pour un semestre et clôture ses notes."
            />

            <Card className="max-w-lg">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <Alert variant="error">{error}</Alert>
                    <Field label="Semestre" required>
                        <Select value={semestreId} onChange={(e) => choisirSemestre(e.target.value)} required>
                            <option value="" disabled>
                                Sélectionner…
                            </option>
                            {semestres.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.libelle} — {s.filiere?.nom}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label="Matricule de l'étudiant" required error={etudiantsError ? extractErrorMessage(etudiantsError, 'Impossible de charger les étudiants.') : undefined}>
                        <Select
                            value={etudiantId}
                            onChange={(e) => setEtudiantId(e.target.value)}
                            disabled={etudiantsLoading || !semestreId}
                            required
                        >
                            <option value="" disabled>
                                {etudiantsLoading ? 'Chargement des étudiants…' : 'Sélectionner un matricule…'}
                            </option>
                            {etudiantsDuProgramme.map((etudiant) => (
                                <option key={etudiant.id} value={etudiant.id}>
                                    {etudiant.matricule} — {etudiant.nom} {etudiant.prenom}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label="Année universitaire" required>
                        <Select value={anneeId} onChange={(e) => setAnneeId(e.target.value)} required>
                            <option value="" disabled>
                                Sélectionner…
                            </option>
                            {annees.map((a) => (
                                <option key={a.id} value={a.id}>
                                    {a.libelle}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Button type="submit" loading={mutation.isPending}>
                        Valider le semestre
                    </Button>
                </form>

                {resultat && (
                    <div className="mt-6 rounded-md bg-slate-50 p-4">
                        <p className="text-sm text-slate-600">
                            Moyenne obtenue : <span className="font-semibold text-slate-900">{resultat.moyenne} / 20</span>
                        </p>
                        <p className="mt-2">
                            {resultat.valide ? <Badge variant="green">Semestre validé</Badge> : <Badge variant="red">Semestre non validé</Badge>}
                        </p>
                        <p className="mt-2 text-sm text-slate-600">{resultat.message}</p>
                    </div>
                )}
            </Card>
        </div>
    );
}

import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import {
    useCoursItem,
    useEnseignantsDuCours,
    useInscritsDuCours,
    useAttachEnseignant,
    useDetachEnseignant,
} from '../features/cours';
import { useEnseignants } from '../features/enseignants';
import { useAnnees, useAnneeCourante } from '../features/annees';
import { extractErrorMessage } from '../lib/api';
import { formatDate } from '../lib/format';
import { Alert, Badge, Button, Card, DataTable, Field, PageHeader, Select } from '../components/ui';

export default function CoursDetailPage() {
    const { id } = useParams();
    const { isAdmin, isScolarite, isEnseignant } = useAuth();
    const { data: coursData, isLoading, error } = useCoursItem(id);
    const { data: anneeCouranteData } = useAnneeCourante();
    const { data: anneesData } = useAnnees();
    const annees = anneesData?.data ?? [];

    const [anneeId, setAnneeId] = useState('');

    useEffect(() => {
        if (!anneeId && anneeCouranteData?.data) {
            setAnneeId(String(anneeCouranteData.data.id));
        }
    }, [anneeCouranteData, anneeId]);

    const cours = coursData?.data;
    const canSeeEnseignants = isAdmin || isScolarite;
    const canSeeInscrits = isAdmin || isScolarite || isEnseignant;

    const enseignantsQuery = useEnseignantsDuCours(id, anneeId ? { annee_id: anneeId } : {}, { enabled: canSeeEnseignants });
    const inscritsQuery = useInscritsDuCours(id, anneeId ? { annee_id: anneeId } : {}, { enabled: canSeeInscrits });
    const { data: enseignantsListData } = useEnseignants({ per_page: 100 }, { enabled: isAdmin });

    const attachMutation = useAttachEnseignant(id);
    const detachMutation = useDetachEnseignant(id);

    const [enseignantASelectionner, setEnseignantASelectionner] = useState('');
    const [attachError, setAttachError] = useState('');

    async function handleAttach(event) {
        event.preventDefault();
        setAttachError('');
        if (!enseignantASelectionner || !anneeId) return;
        try {
            await attachMutation.mutateAsync({ enseignant_id: Number(enseignantASelectionner), annee_id: Number(anneeId) });
            setEnseignantASelectionner('');
        } catch (err) {
            setAttachError(extractErrorMessage(err, "Impossible d'affecter cet enseignant."));
        }
    }

    async function handleDetach(enseignantId) {
        setAttachError('');
        try {
            await detachMutation.mutateAsync({ enseignantId, annee_id: Number(anneeId) });
        } catch (err) {
            setAttachError(extractErrorMessage(err, "Impossible de retirer cet enseignant."));
        }
    }

    if (isLoading) return <Alert variant="info">Chargement du cours…</Alert>;
    if (error) return <Alert variant="error">{extractErrorMessage(error, 'Cours introuvable.')}</Alert>;

    const enseignantsDejaAffectes = new Set((enseignantsQuery.data?.data ?? []).map((e) => e.id));
    const enseignantsDisponibles = (enseignantsListData?.data ?? []).filter((e) => !enseignantsDejaAffectes.has(e.id));

    return (
        <div>
            <PageHeader
                title={`${cours.code} — ${cours.intitule}`}
                description={`Semestre ${cours.semestre?.libelle ?? ''} · Coefficient ${cours.coefficient} · ${cours.credits} crédits`}
                actions={
                    <Link to="/cours" className="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        ← Retour aux cours
                    </Link>
                }
            />

            <div className="mb-6 max-w-xs">
                <Field label="Année universitaire">
                    <Select value={anneeId} onChange={(e) => setAnneeId(e.target.value)}>
                        <option value="">Toutes</option>
                        {annees.map((a) => (
                            <option key={a.id} value={a.id}>
                                {a.libelle}
                            </option>
                        ))}
                    </Select>
                </Field>
            </div>

            {canSeeEnseignants && (
                <Card title="Enseignants affectés" className="mb-6">
                    {attachError && <Alert variant="error" className="mb-4">{attachError}</Alert>}
                    <DataTable
                        loading={enseignantsQuery.isLoading}
                        error={enseignantsQuery.error ? extractErrorMessage(enseignantsQuery.error) : null}
                        rows={enseignantsQuery.data?.data ?? []}
                        emptyTitle="Aucun enseignant affecté pour cette année"
                        columns={[
                            { key: 'nom', header: 'Nom', render: (r) => `${r.nom} ${r.prenom}` },
                            { key: 'specialite', header: 'Spécialité' },
                            { key: 'email', header: 'E-mail' },
                        ]}
                        actions={
                            isAdmin
                                ? (row) => (
                                      <Button
                                          variant="ghost"
                                          onClick={() => handleDetach(row.id)}
                                          loading={detachMutation.isPending}
                                          disabled={!anneeId}
                                      >
                                          Retirer
                                      </Button>
                                  )
                                : undefined
                        }
                    />

                    {isAdmin && (
                        <form onSubmit={handleAttach} className="mt-4 flex flex-wrap items-end gap-3 border-t border-slate-200 pt-4">
                            <div className="w-64">
                                <Field label="Affecter un enseignant">
                                    <Select value={enseignantASelectionner} onChange={(e) => setEnseignantASelectionner(e.target.value)}>
                                        <option value="">Sélectionner…</option>
                                        {enseignantsDisponibles.map((e) => (
                                            <option key={e.id} value={e.id}>
                                                {e.nom} {e.prenom}
                                            </option>
                                        ))}
                                    </Select>
                                </Field>
                            </div>
                            <Button type="submit" loading={attachMutation.isPending} disabled={!anneeId || !enseignantASelectionner}>
                                Affecter
                            </Button>
                            {!anneeId && <span className="text-xs text-slate-500">Choisissez une année pour affecter un enseignant.</span>}
                        </form>
                    )}
                </Card>
            )}

            {canSeeInscrits && (
                <Card title="Étudiants inscrits">
                    <DataTable
                        loading={inscritsQuery.isLoading}
                        error={inscritsQuery.error ? extractErrorMessage(inscritsQuery.error) : null}
                        rows={inscritsQuery.data?.data ?? []}
                        emptyTitle="Aucun étudiant inscrit pour cette période"
                        columns={[
                            {
                                key: 'matricule',
                                header: 'Étudiant',
                                render: (row) => row.etudiant
                                    ? `${row.etudiant.matricule} — ${row.etudiant.nom} ${row.etudiant.prenom}`
                                    : 'Matricule indisponible',
                            },
                            { key: 'date_inscription', header: 'Date d\'inscription', render: (r) => formatDate(r.date_inscription) },
                        ]}
                    />
                </Card>
            )}
        </div>
    );
}

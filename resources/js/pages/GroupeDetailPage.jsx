import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useGroupe, useEtudiantsDuGroupe, useAjouterEtudiantsAuGroupe, useRetirerEtudiantDuGroupe } from '../features/groupes';
import { useEtudiants } from '../features/etudiants';
import { extractErrorMessage } from '../lib/api';
import { Alert, Button, Card, DataTable, Field, Input, PageHeader } from '../components/ui';

export default function GroupeDetailPage() {
    const { id } = useParams();
    const { isAdmin, isScolarite } = useAuth();
    const canWrite = isAdmin || isScolarite;

    const { data: groupeData, isLoading, error } = useGroupe(id);
    const membresQuery = useEtudiantsDuGroupe(id);
    const ajouterMutation = useAjouterEtudiantsAuGroupe(id);
    const retirerMutation = useRetirerEtudiantDuGroupe(id);

    const [search, setSearch] = useState('');
    const { data: candidatsData } = useEtudiants({ q: search, per_page: 10 }, { enabled: canWrite && search.length >= 2 });
    const [actionError, setActionError] = useState('');

    const groupe = groupeData?.data;
    const membres = membresQuery.data?.data ?? [];
    const membreIds = new Set(membres.map((m) => m.id));
    const candidats = (candidatsData?.data ?? []).filter((e) => !membreIds.has(e.id));

    async function handleAjouter(etudiantId) {
        setActionError('');
        try {
            await ajouterMutation.mutateAsync([etudiantId]);
            setSearch('');
        } catch (err) {
            setActionError(extractErrorMessage(err, "Impossible d'ajouter cet étudiant."));
        }
    }

    async function handleRetirer(etudiantId) {
        setActionError('');
        try {
            await retirerMutation.mutateAsync(etudiantId);
        } catch (err) {
            setActionError(extractErrorMessage(err, "Impossible de retirer cet étudiant."));
        }
    }

    if (isLoading) return <Alert variant="info">Chargement du groupe…</Alert>;
    if (error) return <Alert variant="error">{extractErrorMessage(error, 'Groupe introuvable.')}</Alert>;

    return (
        <div>
            <PageHeader
                title={groupe.nom}
                description={`Effectif : ${groupe.effectif_actuel} / ${groupe.effectif_max}`}
                actions={
                    <Link to="/groupes" className="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        ← Retour aux groupes
                    </Link>
                }
            />

            {actionError && <Alert variant="error" className="mb-4">{actionError}</Alert>}

            <Card title="Étudiants du groupe">
                <DataTable
                    loading={membresQuery.isLoading}
                    error={membresQuery.error ? extractErrorMessage(membresQuery.error) : null}
                    rows={membres}
                    emptyTitle="Aucun étudiant dans ce groupe"
                    columns={[
                        { key: 'matricule', header: 'Matricule' },
                        { key: 'nom', header: 'Nom', render: (r) => `${r.nom} ${r.prenom}` },
                    ]}
                    actions={
                        canWrite
                            ? (row) => (
                                  <Button variant="ghost" onClick={() => handleRetirer(row.id)} loading={retirerMutation.isPending}>
                                      Retirer
                                  </Button>
                              )
                            : undefined
                    }
                />
            </Card>

            {canWrite && (
                <Card title="Ajouter un étudiant" className="mt-6">
                    <Field label="Rechercher par nom, prénom ou matricule">
                        <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Au moins 2 caractères…" />
                    </Field>
                    {search.length >= 2 && (
                        <ul className="mt-3 divide-y divide-slate-100">
                            {candidats.length === 0 && <li className="py-2 text-sm text-slate-500">Aucun résultat.</li>}
                            {candidats.map((c) => (
                                <li key={c.id} className="flex items-center justify-between py-2 text-sm">
                                    <span>
                                        {c.matricule} — {c.nom} {c.prenom}
                                    </span>
                                    <Button variant="ghost" onClick={() => handleAjouter(c.id)} loading={ajouterMutation.isPending}>
                                        Ajouter
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            )}
        </div>
    );
}

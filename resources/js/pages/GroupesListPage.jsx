import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useGroupes, useCreateGroupe, useUpdateGroupe, useRemoveGroupe } from '../features/groupes';
import { useFilieres } from '../features/filieres';
import { useAnnees } from '../features/annees';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { Alert, Button, Card, ConfirmDialog, DataTable, Field, Input, Modal, PageHeader, Select } from '../components/ui';

const EMPTY_FORM = { nom: '', filiere_id: '', annee_id: '', effectif_max: '' };

export default function GroupesListPage() {
    const { isAdmin, isScolarite } = useAuth();
    const canWrite = isAdmin || isScolarite;

    const { data: filieresData } = useFilieres();
    const { data: anneesData } = useAnnees();
    const filieres = filieresData?.data ?? [];
    const annees = anneesData?.data ?? [];

    const [filiereFiltre, setFiliereFiltre] = useState('');
    const [anneeFiltre, setAnneeFiltre] = useState('');
    const [page, setPage] = useState(1);

    const params = { page, ...(filiereFiltre && { filiere_id: filiereFiltre }), ...(anneeFiltre && { annee_id: anneeFiltre }) };
    const { data, isLoading, error } = useGroupes(params);

    const createMutation = useCreateGroupe();
    const updateMutation = useUpdateGroupe();
    const removeMutation = useRemoveGroupe();

    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState(EMPTY_FORM);
    const [formError, setFormError] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});
    const [toDelete, setToDelete] = useState(null);

    const rows = data?.data ?? [];

    function openCreate() {
        setEditing(null);
        setForm(EMPTY_FORM);
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    function openEdit(groupe) {
        setEditing(groupe);
        setForm({
            nom: groupe.nom,
            filiere_id: String(groupe.filiere_id),
            annee_id: String(groupe.annee_id),
            effectif_max: String(groupe.effectif_max),
        });
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});
        const payload = {
            nom: form.nom,
            filiere_id: Number(form.filiere_id),
            annee_id: Number(form.annee_id),
            effectif_max: Number(form.effectif_max),
        };
        try {
            if (editing) {
                await updateMutation.mutateAsync({ id: editing.id, payload });
            } else {
                await createMutation.mutateAsync(payload);
            }
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer le groupe."));
            setFieldErrors(extractFieldErrors(err));
        }
    }

    async function confirmDelete() {
        try {
            await removeMutation.mutateAsync(toDelete.id);
            setToDelete(null);
        } catch (err) {
            setFormError(extractErrorMessage(err, 'Suppression impossible.'));
            setToDelete(null);
        }
    }

    const saving = createMutation.isPending || updateMutation.isPending;

    return (
        <div>
            <PageHeader
                title="Groupes"
                description="Groupes d'étudiants par filière et année."
                actions={canWrite && <Button onClick={openCreate}>Nouveau groupe</Button>}
            />

            <Card
                actions={
                    <div className="flex gap-2">
                        <Select value={filiereFiltre} onChange={(e) => { setFiliereFiltre(e.target.value); setPage(1); }} className="w-48">
                            <option value="">Toutes les filières</option>
                            {filieres.map((f) => (
                                <option key={f.id} value={f.id}>
                                    {f.code}
                                </option>
                            ))}
                        </Select>
                        <Select value={anneeFiltre} onChange={(e) => { setAnneeFiltre(e.target.value); setPage(1); }} className="w-40">
                            <option value="">Toutes les années</option>
                            {annees.map((a) => (
                                <option key={a.id} value={a.id}>
                                    {a.libelle}
                                </option>
                            ))}
                        </Select>
                    </div>
                }
            >
                {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        {
                            key: 'nom',
                            header: 'Nom',
                            render: (r) => (
                                <Link to={`/groupes/${r.id}`} className="font-medium text-indigo-600 hover:text-indigo-500">
                                    {r.nom}
                                </Link>
                            ),
                        },
                        { key: 'effectif', header: 'Effectif', render: (r) => `${r.effectif_actuel} / ${r.effectif_max}` },
                    ]}
                    actions={(row) => (
                        <>
                            <Link to={`/groupes/${row.id}`} className="text-sm font-medium text-slate-600 hover:text-slate-900">
                                Membres
                            </Link>
                            {canWrite && (
                                <>
                                    <Button variant="ghost" onClick={() => openEdit(row)}>
                                        Modifier
                                    </Button>
                                    <Button variant="ghost" onClick={() => setToDelete(row)}>
                                        Supprimer
                                    </Button>
                                </>
                            )}
                        </>
                    )}
                />

                <div className="mt-4 flex items-center justify-between text-sm text-slate-600">
                    <Button variant="secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                        Précédent
                    </Button>
                    <span>Page {page}</span>
                    <Button variant="secondary" disabled={rows.length === 0} onClick={() => setPage((p) => p + 1)}>
                        Suivant
                    </Button>
                </div>
            </Card>

            <Modal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                title={editing ? 'Modifier le groupe' : 'Nouveau groupe'}
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="groupe-form" loading={saving}>
                            Enregistrer
                        </Button>
                    </>
                }
            >
                <form id="groupe-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <Field label="Nom" required error={fieldErrors.nom?.[0]}>
                        <Input value={form.nom} maxLength={255} onChange={(e) => setForm({ ...form, nom: e.target.value })} required />
                    </Field>
                    <Field label="Filière" required error={fieldErrors.filiere_id?.[0]}>
                        <Select value={form.filiere_id} onChange={(e) => setForm({ ...form, filiere_id: e.target.value })} required>
                            <option value="" disabled>
                                Sélectionner…
                            </option>
                            {filieres.map((f) => (
                                <option key={f.id} value={f.id}>
                                    {f.code} — {f.nom}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label="Année universitaire" required error={fieldErrors.annee_id?.[0]}>
                        <Select value={form.annee_id} onChange={(e) => setForm({ ...form, annee_id: e.target.value })} required>
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
                    <Field label="Effectif maximum" required error={fieldErrors.effectif_max?.[0]}>
                        <Input
                            type="number"
                            min="1"
                            value={form.effectif_max}
                            onChange={(e) => setForm({ ...form, effectif_max: e.target.value })}
                            required
                        />
                    </Field>
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDelete}
                title="Supprimer le groupe"
                message={`Confirmez-vous la suppression du groupe « ${toDelete?.nom} » ?`}
                loading={removeMutation.isPending}
                onCancel={() => setToDelete(null)}
                onConfirm={confirmDelete}
            />
        </div>
    );
}

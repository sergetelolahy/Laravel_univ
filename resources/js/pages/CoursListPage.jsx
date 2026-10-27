import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useCoursList, useCreateCours, useUpdateCours, useRemoveCours } from '../features/cours';
import { useSemestres } from '../features/semestres';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { Alert, Button, Card, ConfirmDialog, DataTable, Field, Input, Modal, PageHeader, Select } from '../components/ui';

const EMPTY_FORM = { code: '', intitule: '', semestre_id: '', coefficient: '', credits: '' };

export default function CoursListPage() {
    const { isAdmin } = useAuth();
    const [search, setSearch] = useState('');
    const { data: semestresData } = useSemestres();
    const semestres = semestresData?.data ?? [];

    const { data, isLoading, error } = useCoursList(search ? { q: search } : {});
    const createMutation = useCreateCours();
    const updateMutation = useUpdateCours();
    const removeMutation = useRemoveCours();

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

    function openEdit(cours) {
        setEditing(cours);
        setForm({
            code: cours.code,
            intitule: cours.intitule,
            semestre_id: String(cours.semestre_id),
            coefficient: String(cours.coefficient),
            credits: String(cours.credits),
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
            code: form.code,
            intitule: form.intitule,
            semestre_id: Number(form.semestre_id),
            coefficient: Number(form.coefficient),
            credits: Number(form.credits),
        };
        try {
            if (editing) {
                await updateMutation.mutateAsync({ id: editing.id, payload });
            } else {
                await createMutation.mutateAsync(payload);
            }
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer le cours."));
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
                title="Cours"
                description="Catalogue des cours et de leurs enseignants affectés."
                actions={isAdmin && <Button onClick={openCreate}>Nouveau cours</Button>}
            />

            <Card actions={<Input placeholder="Rechercher un cours…" value={search} onChange={(e) => setSearch(e.target.value)} className="w-64" />}>
                {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        {
                            key: 'code',
                            header: 'Code',
                            render: (r) => (
                                <Link to={`/cours/${r.id}`} className="font-medium text-indigo-600 hover:text-indigo-500">
                                    {r.code}
                                </Link>
                            ),
                        },
                        { key: 'intitule', header: 'Intitulé' },
                        { key: 'semestre', header: 'Semestre', render: (r) => r.semestre?.libelle ?? '—' },
                        { key: 'filiere', header: 'Filière', render: (r) => r.semestre?.filiere?.nom ?? '—' },
                        { key: 'coefficient', header: 'Coeff.' },
                        { key: 'credits', header: 'Crédits' },
                    ]}
                    actions={(row) => (
                        <>
                            <Link to={`/cours/${row.id}`} className="text-sm font-medium text-slate-600 hover:text-slate-900">
                                Détails
                            </Link>
                            {isAdmin && (
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
            </Card>

            <Modal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                title={editing ? 'Modifier le cours' : 'Nouveau cours'}
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="cours-form" loading={saving}>
                            Enregistrer
                        </Button>
                    </>
                }
            >
                <form id="cours-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <Field label="Code" required error={fieldErrors.code?.[0]}>
                        <Input value={form.code} maxLength={50} onChange={(e) => setForm({ ...form, code: e.target.value })} required />
                    </Field>
                    <Field label="Intitulé" required error={fieldErrors.intitule?.[0]}>
                        <Input value={form.intitule} maxLength={255} onChange={(e) => setForm({ ...form, intitule: e.target.value })} required />
                    </Field>
                    <Field label="Semestre" required error={fieldErrors.semestre_id?.[0]}>
                        <Select value={form.semestre_id} onChange={(e) => setForm({ ...form, semestre_id: e.target.value })} required>
                            <option value="" disabled>
                                Sélectionner un semestre
                            </option>
                            {semestres.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.libelle} — {s.filiere?.nom}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Coefficient" required error={fieldErrors.coefficient?.[0]}>
                            <Input
                                type="number"
                                step="0.01"
                                min="0.01"
                                value={form.coefficient}
                                onChange={(e) => setForm({ ...form, coefficient: e.target.value })}
                                required
                            />
                        </Field>
                        <Field label="Crédits" required error={fieldErrors.credits?.[0]}>
                            <Input
                                type="number"
                                min="0"
                                value={form.credits}
                                onChange={(e) => setForm({ ...form, credits: e.target.value })}
                                required
                            />
                        </Field>
                    </div>
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDelete}
                title="Supprimer le cours"
                message={`Confirmez-vous la suppression du cours « ${toDelete?.code} » ?`}
                loading={removeMutation.isPending}
                onCancel={() => setToDelete(null)}
                onConfirm={confirmDelete}
            />
        </div>
    );
}

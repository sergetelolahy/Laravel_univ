import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useSeances, useCreateSeance, useUpdateSeance, useRemoveSeance } from '../features/seances';
import { useCoursList } from '../features/cours';
import { useGroupes } from '../features/groupes';
import { useSalles } from '../features/salles';
import { useEnseignants } from '../features/enseignants';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { Alert, Button, Card, ConfirmDialog, DataTable, Field, Modal, PageHeader, Select } from '../components/ui';

const EMPTY_FORM = { cours_id: '', groupe_id: '', enseignant_id: '', salle_id: '', debut: '', fin: '' };

function toLocalInput(value) {
    return value ? value.replace(' ', 'T').slice(0, 16) : '';
}

function toApiDateTime(value) {
    return value ? `${value.replace('T', ' ')}:00` : '';
}

export default function SeancesPage() {
    const { isAdmin } = useAuth();
    const [page, setPage] = useState(1);
    const { data, isLoading, error } = useSeances({ page });
    const { data: coursData } = useCoursList();
    const { data: groupesData } = useGroupes({ per_page: 100 });
    const { data: sallesData } = useSalles();
    const { data: enseignantsData } = useEnseignants({ per_page: 100 });

    const createMutation = useCreateSeance();
    const updateMutation = useUpdateSeance();
    const removeMutation = useRemoveSeance();

    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState(EMPTY_FORM);
    const [formError, setFormError] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});
    const [toDelete, setToDelete] = useState(null);

    const rows = data?.data ?? [];
    const meta = data?.meta;
    const coursListe = coursData?.data ?? [];
    const groupesListe = groupesData?.data ?? [];
    const sallesListe = sallesData?.data ?? [];
    const enseignantsListe = enseignantsData?.data ?? [];

    function openCreate() {
        setEditing(null);
        setForm(EMPTY_FORM);
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    function openEdit(seance) {
        setEditing(seance);
        setForm({
            cours_id: String(seance.cours?.id ?? ''),
            groupe_id: String(seance.groupe?.id ?? ''),
            enseignant_id: String(seance.enseignant?.id ?? ''),
            salle_id: String(seance.salle?.id ?? ''),
            debut: toLocalInput(seance.debut),
            fin: toLocalInput(seance.fin),
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
            cours_id: Number(form.cours_id),
            groupe_id: Number(form.groupe_id),
            enseignant_id: Number(form.enseignant_id),
            salle_id: Number(form.salle_id),
            debut: toApiDateTime(form.debut),
            fin: toApiDateTime(form.fin),
        };
        try {
            if (editing) {
                await updateMutation.mutateAsync({ id: editing.id, payload });
            } else {
                await createMutation.mutateAsync(payload);
            }
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer la séance."));
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
                title="Emplois du temps"
                description="Planification des séances (cours, groupe, enseignant, salle)."
                actions={isAdmin && <Button onClick={openCreate}>Nouvelle séance</Button>}
            />

            <Card>
                {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        { key: 'debut', header: 'Début', render: (r) => r.debut },
                        { key: 'fin', header: 'Fin', render: (r) => r.fin.slice(11, 16) },
                        { key: 'cours', header: 'Cours', render: (r) => r.cours?.code },
                        { key: 'groupe', header: 'Groupe', render: (r) => r.groupe?.nom },
                        { key: 'enseignant', header: 'Enseignant', render: (r) => `${r.enseignant?.nom ?? ''} ${r.enseignant?.prenom ?? ''}` },
                        { key: 'salle', header: 'Salle', render: (r) => r.salle?.nom },
                    ]}
                    actions={
                        isAdmin
                            ? (row) => (
                                  <>
                                      <Button variant="ghost" onClick={() => openEdit(row)}>
                                          Modifier
                                      </Button>
                                      <Button variant="ghost" onClick={() => setToDelete(row)}>
                                          Supprimer
                                      </Button>
                                  </>
                              )
                            : undefined
                    }
                />

                {meta && meta.last_page > 1 && (
                    <div className="mt-4 flex items-center justify-between text-sm text-slate-600">
                        <Button variant="secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                            Précédent
                        </Button>
                        <span>
                            Page {meta.current_page} / {meta.last_page}
                        </span>
                        <Button variant="secondary" disabled={page >= meta.last_page} onClick={() => setPage((p) => p + 1)}>
                            Suivant
                        </Button>
                    </div>
                )}
            </Card>

            <Modal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                title={editing ? 'Modifier la séance' : 'Nouvelle séance'}
                size="lg"
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="seance-form" loading={saving}>
                            Enregistrer
                        </Button>
                    </>
                }
            >
                <form id="seance-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Cours" required error={fieldErrors.cours_id?.[0]}>
                            <Select value={form.cours_id} onChange={(e) => setForm({ ...form, cours_id: e.target.value })} required>
                                <option value="" disabled>
                                    Sélectionner…
                                </option>
                                {coursListe.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.code} — {c.intitule}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label="Groupe" required error={fieldErrors.groupe_id?.[0]}>
                            <Select value={form.groupe_id} onChange={(e) => setForm({ ...form, groupe_id: e.target.value })} required>
                                <option value="" disabled>
                                    Sélectionner…
                                </option>
                                {groupesListe.map((g) => (
                                    <option key={g.id} value={g.id}>
                                        {g.nom}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label="Enseignant" required error={fieldErrors.enseignant_id?.[0]}>
                            <Select value={form.enseignant_id} onChange={(e) => setForm({ ...form, enseignant_id: e.target.value })} required>
                                <option value="" disabled>
                                    Sélectionner…
                                </option>
                                {enseignantsListe.map((e) => (
                                    <option key={e.id} value={e.id}>
                                        {e.nom} {e.prenom}
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label="Salle" required error={fieldErrors.salle_id?.[0]}>
                            <Select value={form.salle_id} onChange={(e) => setForm({ ...form, salle_id: e.target.value })} required>
                                <option value="" disabled>
                                    Sélectionner…
                                </option>
                                {sallesListe.map((s) => (
                                    <option key={s.id} value={s.id}>
                                        {s.nom} ({s.capacite} places)
                                    </option>
                                ))}
                            </Select>
                        </Field>
                        <Field label="Début" required error={fieldErrors.debut?.[0]}>
                            <input
                                type="datetime-local"
                                className="block w-full rounded-md border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600"
                                value={form.debut}
                                onChange={(e) => setForm({ ...form, debut: e.target.value })}
                                required
                            />
                        </Field>
                        <Field label="Fin" required error={fieldErrors.fin?.[0]}>
                            <input
                                type="datetime-local"
                                className="block w-full rounded-md border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600"
                                value={form.fin}
                                onChange={(e) => setForm({ ...form, fin: e.target.value })}
                                required
                            />
                        </Field>
                    </div>
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDelete}
                title="Supprimer la séance"
                message="Confirmez-vous la suppression de cette séance ?"
                loading={removeMutation.isPending}
                onCancel={() => setToDelete(null)}
                onConfirm={confirmDelete}
            />
        </div>
    );
}

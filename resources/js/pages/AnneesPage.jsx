import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useAnnees, useCreateAnnee, useUpdateAnnee, useRemoveAnnee, useSetAnneeCourante } from '../features/annees';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { formatDate } from '../lib/format';
import { Alert, Badge, Button, Card, Checkbox, ConfirmDialog, DataTable, Field, Input, Modal, PageHeader } from '../components/ui';

const EMPTY_FORM = { libelle: '', date_debut: '', date_fin: '', courante: false };

export default function AnneesPage() {
    const { isAdmin } = useAuth();
    const { data, isLoading, error } = useAnnees();
    const createMutation = useCreateAnnee();
    const updateMutation = useUpdateAnnee();
    const removeMutation = useRemoveAnnee();
    const setCouranteMutation = useSetAnneeCourante();

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

    function openEdit(annee) {
        setEditing(annee);
        setForm({
            libelle: annee.libelle,
            date_debut: annee.date_debut,
            date_fin: annee.date_fin,
            courante: !!annee.courante,
        });
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});

        if (form.date_debut && form.date_fin && form.date_fin <= form.date_debut) {
            setFormError('La date de fin doit être strictement postérieure à la date de début.');
            return;
        }

        try {
            if (editing) {
                await updateMutation.mutateAsync({ id: editing.id, payload: form });
            } else {
                await createMutation.mutateAsync(form);
            }
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer l'année."));
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
                title="Années universitaires"
                description="Gestion des années et de l'année courante."
                actions={isAdmin && <Button onClick={openCreate}>Nouvelle année</Button>}
            />

            <Card>
                {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        { key: 'libelle', header: 'Libellé' },
                        { key: 'date_debut', header: 'Début', render: (r) => formatDate(r.date_debut) },
                        { key: 'date_fin', header: 'Fin', render: (r) => formatDate(r.date_fin) },
                        {
                            key: 'courante',
                            header: 'Statut',
                            render: (r) => (r.courante ? <Badge variant="green">Courante</Badge> : null),
                        },
                    ]}
                    actions={
                        isAdmin
                            ? (row) => (
                                  <>
                                      {!row.courante && (
                                          <Button
                                              variant="ghost"
                                              onClick={() => setCouranteMutation.mutate(row.id)}
                                              loading={setCouranteMutation.isPending}
                                          >
                                              Définir courante
                                          </Button>
                                      )}
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
            </Card>

            <Modal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                title={editing ? "Modifier l'année" : 'Nouvelle année universitaire'}
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="annee-form" loading={saving}>
                            Enregistrer
                        </Button>
                    </>
                }
            >
                <form id="annee-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <Field label="Libellé" required hint="Ex. 2026-2027" error={fieldErrors.libelle?.[0]}>
                        <Input
                            value={form.libelle}
                            maxLength={20}
                            onChange={(e) => setForm({ ...form, libelle: e.target.value })}
                            required
                        />
                    </Field>
                    <Field label="Date de début" required error={fieldErrors.date_debut?.[0]}>
                        <Input
                            type="date"
                            value={form.date_debut}
                            onChange={(e) => setForm({ ...form, date_debut: e.target.value })}
                            required
                        />
                    </Field>
                    <Field label="Date de fin" required error={fieldErrors.date_fin?.[0]}>
                        <Input
                            type="date"
                            value={form.date_fin}
                            onChange={(e) => setForm({ ...form, date_fin: e.target.value })}
                            required
                        />
                    </Field>
                    <Checkbox
                        label="Définir comme année courante"
                        checked={form.courante}
                        onChange={(e) => setForm({ ...form, courante: e.target.checked })}
                    />
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDelete}
                title="Supprimer l'année"
                message={`Confirmez-vous la suppression de l'année « ${toDelete?.libelle} » ?`}
                loading={removeMutation.isPending}
                onCancel={() => setToDelete(null)}
                onConfirm={confirmDelete}
            />
        </div>
    );
}

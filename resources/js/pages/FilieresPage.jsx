import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useFilieres, useCreateFiliere, useUpdateFiliere, useRemoveFiliere } from '../features/filieres';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { Alert, Button, Card, ConfirmDialog, DataTable, Field, Input, Modal, PageHeader } from '../components/ui';

const EMPTY_FORM = { code: '', nom: '' };

export default function FilieresPage() {
    const { isAdmin } = useAuth();
    const { data, isLoading, error } = useFilieres();
    const createMutation = useCreateFiliere();
    const updateMutation = useUpdateFiliere();
    const removeMutation = useRemoveFiliere();

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

    function openEdit(filiere) {
        setEditing(filiere);
        setForm({ code: filiere.code, nom: filiere.nom });
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});
        try {
            if (editing) {
                await updateMutation.mutateAsync({ id: editing.id, payload: form });
            } else {
                await createMutation.mutateAsync(form);
            }
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer la filière."));
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
                title="Filières"
                description="Catalogue des filières de formation."
                actions={isAdmin && <Button onClick={openCreate}>Nouvelle filière</Button>}
            />

            <Card>
                {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        { key: 'code', header: 'Code' },
                        { key: 'nom', header: 'Nom' },
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
            </Card>

            <Modal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                title={editing ? 'Modifier la filière' : 'Nouvelle filière'}
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="filiere-form" loading={saving}>
                            Enregistrer
                        </Button>
                    </>
                }
            >
                <form id="filiere-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <Field label="Code" required error={fieldErrors.code?.[0]}>
                        <Input
                            value={form.code}
                            maxLength={50}
                            onChange={(e) => setForm({ ...form, code: e.target.value })}
                            required
                        />
                    </Field>
                    <Field label="Nom" required error={fieldErrors.nom?.[0]}>
                        <Input
                            value={form.nom}
                            maxLength={255}
                            onChange={(e) => setForm({ ...form, nom: e.target.value })}
                            required
                        />
                    </Field>
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDelete}
                title="Supprimer la filière"
                message={`Confirmez-vous la suppression de la filière « ${toDelete?.nom} » ?`}
                loading={removeMutation.isPending}
                onCancel={() => setToDelete(null)}
                onConfirm={confirmDelete}
            />
        </div>
    );
}

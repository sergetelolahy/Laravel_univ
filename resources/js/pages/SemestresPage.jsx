import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useSemestres, useCreateSemestre, useUpdateSemestre, useRemoveSemestre } from '../features/semestres';
import { useFilieres } from '../features/filieres';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { Alert, Button, Card, ConfirmDialog, DataTable, Field, Input, Modal, PageHeader, Select } from '../components/ui';

const EMPTY_FORM = { filiere_id: '', numero: '' };

export default function SemestresPage() {
    const { isAdmin } = useAuth();
    const { data: filieresData } = useFilieres();
    const filieres = filieresData?.data ?? [];
    const [filiereFiltre, setFiliereFiltre] = useState('');

    const { data, isLoading, error } = useSemestres(filiereFiltre ? { filiere_id: filiereFiltre } : {});
    const createMutation = useCreateSemestre();
    const updateMutation = useUpdateSemestre();
    const removeMutation = useRemoveSemestre();

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

    function openEdit(semestre) {
        setEditing(semestre);
        setForm({ filiere_id: String(semestre.filiere_id), numero: String(semestre.numero) });
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});
        const payload = { filiere_id: Number(form.filiere_id), numero: Number(form.numero) };
        try {
            if (editing) {
                await updateMutation.mutateAsync({ id: editing.id, payload });
            } else {
                await createMutation.mutateAsync(payload);
            }
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer le semestre."));
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
                title="Semestres"
                description="Semestres rattachés à chaque filière."
                actions={isAdmin && <Button onClick={openCreate}>Nouveau semestre</Button>}
            />

            <Card
                actions={
                    <Select value={filiereFiltre} onChange={(e) => setFiliereFiltre(e.target.value)} className="w-56">
                        <option value="">Toutes les filières</option>
                        {filieres.map((f) => (
                            <option key={f.id} value={f.id}>
                                {f.code} — {f.nom}
                            </option>
                        ))}
                    </Select>
                }
            >
                {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        { key: 'libelle', header: 'Semestre' },
                        { key: 'filiere', header: 'Filière', render: (r) => r.filiere?.nom ?? '—' },
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
                title={editing ? 'Modifier le semestre' : 'Nouveau semestre'}
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="semestre-form" loading={saving}>
                            Enregistrer
                        </Button>
                    </>
                }
            >
                <form id="semestre-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <Field label="Filière" required error={fieldErrors.filiere_id?.[0]}>
                        <Select
                            value={form.filiere_id}
                            onChange={(e) => setForm({ ...form, filiere_id: e.target.value })}
                            required
                        >
                            <option value="" disabled>
                                Sélectionner une filière
                            </option>
                            {filieres.map((f) => (
                                <option key={f.id} value={f.id}>
                                    {f.code} — {f.nom}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    <Field label="Numéro du semestre" required hint="Entre 1 et 10" error={fieldErrors.numero?.[0]}>
                        <Input
                            type="number"
                            min={1}
                            max={10}
                            value={form.numero}
                            onChange={(e) => setForm({ ...form, numero: e.target.value })}
                            required
                        />
                    </Field>
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDelete}
                title="Supprimer le semestre"
                message={`Confirmez-vous la suppression de ce semestre ?`}
                loading={removeMutation.isPending}
                onCancel={() => setToDelete(null)}
                onConfirm={confirmDelete}
            />
        </div>
    );
}

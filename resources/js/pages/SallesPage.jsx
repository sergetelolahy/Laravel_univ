import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useSalles, useCreateSalle, useUpdateSalle, useRemoveSalle, useSallesDisponibles, useSalleOccupation } from '../features/salles';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { Alert, Badge, Button, Card, ConfirmDialog, DataTable, Field, Input, Modal, PageHeader } from '../components/ui';

const EMPTY_FORM = { nom: '', capacite: '' };

export default function SallesPage() {
    const { isAdmin, isScolarite } = useAuth();
    const { data, isLoading, error } = useSalles();
    const createMutation = useCreateSalle();
    const updateMutation = useUpdateSalle();
    const removeMutation = useRemoveSalle();

    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [form, setForm] = useState(EMPTY_FORM);
    const [formError, setFormError] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});
    const [toDelete, setToDelete] = useState(null);

    const [disponibiliteOpen, setDisponibiliteOpen] = useState(false);
    const [occupationSalle, setOccupationSalle] = useState(null);

    const rows = data?.data ?? [];
    const canManageDisponibilite = isAdmin || isScolarite;

    function openCreate() {
        setEditing(null);
        setForm(EMPTY_FORM);
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    function openEdit(salle) {
        setEditing(salle);
        setForm({ nom: salle.nom, capacite: String(salle.capacite) });
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});
        const payload = { nom: form.nom, capacite: Number(form.capacite) };
        try {
            if (editing) {
                await updateMutation.mutateAsync({ id: editing.id, payload });
            } else {
                await createMutation.mutateAsync(payload);
            }
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer la salle."));
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
                title="Salles"
                description="Salles de cours et leur capacité."
                actions={
                    <div className="flex gap-2">
                        {canManageDisponibilite && (
                            <Button variant="secondary" onClick={() => setDisponibiliteOpen(true)}>
                                Chercher une salle libre
                            </Button>
                        )}
                        {isAdmin && <Button onClick={openCreate}>Nouvelle salle</Button>}
                    </div>
                }
            />

            <Card>
                {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={rows}
                    columns={[
                        { key: 'nom', header: 'Nom' },
                        { key: 'capacite', header: 'Capacité' },
                    ]}
                    actions={(row) => (
                        <>
                            {canManageDisponibilite && (
                                <Button variant="ghost" onClick={() => setOccupationSalle(row)}>
                                    Occupation
                                </Button>
                            )}
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
                title={editing ? 'Modifier la salle' : 'Nouvelle salle'}
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="salle-form" loading={saving}>
                            Enregistrer
                        </Button>
                    </>
                }
            >
                <form id="salle-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <Field label="Nom" required error={fieldErrors.nom?.[0]}>
                        <Input value={form.nom} maxLength={100} onChange={(e) => setForm({ ...form, nom: e.target.value })} required />
                    </Field>
                    <Field label="Capacité" required error={fieldErrors.capacite?.[0]}>
                        <Input
                            type="number"
                            min="1"
                            value={form.capacite}
                            onChange={(e) => setForm({ ...form, capacite: e.target.value })}
                            required
                        />
                    </Field>
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDelete}
                title="Supprimer la salle"
                message={`Confirmez-vous la suppression de la salle « ${toDelete?.nom} » ?`}
                loading={removeMutation.isPending}
                onCancel={() => setToDelete(null)}
                onConfirm={confirmDelete}
            />

            {disponibiliteOpen && <DisponibiliteModal onClose={() => setDisponibiliteOpen(false)} />}
            {occupationSalle && <OccupationModal salle={occupationSalle} onClose={() => setOccupationSalle(null)} />}
        </div>
    );
}

function DisponibiliteModal({ onClose }) {
    const [debut, setDebut] = useState('');
    const [fin, setFin] = useState('');
    const [capaciteMin, setCapaciteMin] = useState('');
    const [params, setParams] = useState(null);

    const { data, isLoading, error } = useSallesDisponibles(params ?? {});

    function handleSubmit(event) {
        event.preventDefault();
        setParams({
            debut: `${debut}:00`,
            fin: `${fin}:00`,
            capacite_min: capaciteMin || 0,
        });
    }

    return (
        <Modal open onClose={onClose} title="Rechercher une salle libre" size="lg">
            <form onSubmit={handleSubmit} className="mb-4 grid gap-3 sm:grid-cols-3">
                <Field label="Début" required>
                    <Input type="datetime-local" value={debut} onChange={(e) => setDebut(e.target.value)} required />
                </Field>
                <Field label="Fin" required>
                    <Input type="datetime-local" value={fin} onChange={(e) => setFin(e.target.value)} required />
                </Field>
                <Field label="Capacité minimale">
                    <Input type="number" min="0" value={capaciteMin} onChange={(e) => setCapaciteMin(e.target.value)} />
                </Field>
                <div className="sm:col-span-3">
                    <Button type="submit">Rechercher</Button>
                </div>
            </form>

            {params && (
                <DataTable
                    loading={isLoading}
                    error={error ? extractErrorMessage(error) : null}
                    rows={data?.data ?? []}
                    emptyTitle="Aucune salle disponible sur ce créneau"
                    columns={[
                        { key: 'nom', header: 'Nom' },
                        { key: 'capacite', header: 'Capacité' },
                    ]}
                />
            )}
        </Modal>
    );
}

function OccupationModal({ salle, onClose }) {
    const { data, isLoading, error } = useSalleOccupation(salle.id);
    const jours = data?.data?.jours ?? [];

    return (
        <Modal open onClose={onClose} title={`Occupation — ${salle.nom}`} size="lg">
            {isLoading && <Alert variant="info">Chargement…</Alert>}
            {error && <Alert variant="error">{extractErrorMessage(error)}</Alert>}
            {!isLoading && !error && jours.every((j) => j.seances.length === 0) && (
                <Alert variant="info">Aucune séance planifiée cette semaine pour cette salle.</Alert>
            )}
            <div className="space-y-4">
                {jours
                    .filter((j) => j.seances.length > 0)
                    .map((jour) => (
                        <div key={jour.date}>
                            <p className="mb-1 text-sm font-semibold text-slate-700">{jour.date}</p>
                            <ul className="space-y-1">
                                {jour.seances.map((s) => (
                                    <li key={s.id} className="rounded-md bg-slate-50 px-3 py-2 text-sm">
                                        <span className="font-medium">{s.cours?.code}</span> — {s.groupe?.nom} ·{' '}
                                        {s.debut.slice(11, 16)}–{s.fin.slice(11, 16)}
                                        <Badge variant="slate" className="ml-2">
                                            {s.enseignant?.nom} {s.enseignant?.prenom}
                                        </Badge>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
            </div>
        </Modal>
    );
}

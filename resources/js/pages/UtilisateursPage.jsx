import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useUtilisateurs, useCreateUtilisateur, useDesactiverUtilisateur } from '../features/utilisateurs';
import { useFilieres } from '../features/filieres';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { ROLE_LABELS, ADMIN, SCOLARITE, ENSEIGNANT, ETUDIANT } from '../lib/roles';
import { formatDateTime } from '../lib/format';
import { Alert, Badge, Button, Card, Checkbox, ConfirmDialog, DataTable, Field, Input, Modal, PageHeader, Select } from '../components/ui';

const ALL_ROLE_OPTIONS = [ADMIN, SCOLARITE, ENSEIGNANT, ETUDIANT];

const EMPTY_FORM = {
    email: '',
    password: '',
    role: '',
    actif: true,
    matricule: '',
    nom: '',
    prenom: '',
    filiere_id: '',
    date_naissance: '',
    specialite: '',
};

export default function UtilisateursPage() {
    const { isAdmin } = useAuth();
    const [roleFiltre, setRoleFiltre] = useState('');
    const { data, isLoading, error } = useUtilisateurs(roleFiltre ? { role: roleFiltre } : {}, { enabled: isAdmin });
    const { data: filieresData } = useFilieres();
    const filieres = filieresData?.data ?? [];

    const createMutation = useCreateUtilisateur();
    const desactiverMutation = useDesactiverUtilisateur();

    const [modalOpen, setModalOpen] = useState(false);
    const [form, setForm] = useState(EMPTY_FORM);
    const [formError, setFormError] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});
    const [toDeactivate, setToDeactivate] = useState(null);

    const roleOptions = isAdmin ? ALL_ROLE_OPTIONS : [ENSEIGNANT, ETUDIANT];
    const rows = data?.data ?? [];

    function openCreate() {
        setForm(EMPTY_FORM);
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});

        const payload = {
            email: form.email,
            password: form.password,
            role: form.role,
            actif: form.actif,
        };

        if (form.role === ETUDIANT) {
            Object.assign(payload, {
                matricule: form.matricule,
                nom: form.nom,
                prenom: form.prenom,
                filiere_id: Number(form.filiere_id),
                ...(form.date_naissance && { date_naissance: form.date_naissance }),
            });
        }

        if (form.role === ENSEIGNANT) {
            Object.assign(payload, {
                nom: form.nom,
                prenom: form.prenom,
                ...(form.specialite && { specialite: form.specialite }),
            });
        }

        try {
            await createMutation.mutateAsync(payload);
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible de créer l'utilisateur."));
            setFieldErrors(extractFieldErrors(err));
        }
    }

    async function confirmDeactivate() {
        try {
            await desactiverMutation.mutateAsync(toDeactivate.id);
            setToDeactivate(null);
        } catch (err) {
            setFormError(extractErrorMessage(err, 'Désactivation impossible.'));
            setToDeactivate(null);
        }
    }

    return (
        <div>
            <PageHeader
                title="Comptes utilisateurs"
                description="Création et gestion des comptes de l'application."
                actions={<Button onClick={openCreate}>Nouveau compte</Button>}
            />

            {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}

            {isAdmin ? (
                <Card
                    actions={
                        <Select value={roleFiltre} onChange={(e) => setRoleFiltre(e.target.value)} className="w-56">
                            <option value="">Tous les rôles</option>
                            {ALL_ROLE_OPTIONS.map((r) => (
                                <option key={r} value={r}>
                                    {ROLE_LABELS[r]}
                                </option>
                            ))}
                        </Select>
                    }
                >
                    <DataTable
                        loading={isLoading}
                        error={error ? extractErrorMessage(error) : null}
                        rows={rows}
                        columns={[
                            { key: 'email', header: 'E-mail' },
                            { key: 'role', header: 'Rôle', render: (r) => <Badge variant="indigo">{ROLE_LABELS[r.role]}</Badge> },
                            {
                                key: 'actif',
                                header: 'Statut',
                                render: (r) => (r.actif ? <Badge variant="green">Actif</Badge> : <Badge variant="red">Désactivé</Badge>),
                            },
                            { key: 'derniere_connexion', header: 'Dernière connexion', render: (r) => formatDateTime(r.derniere_connexion) },
                        ]}
                        actions={(row) =>
                            row.actif && (
                                <Button variant="ghost" onClick={() => setToDeactivate(row)}>
                                    Désactiver
                                </Button>
                            )
                        }
                    />
                </Card>
            ) : (
                <Alert variant="info">
                    Vous pouvez créer des comptes Enseignant ou Étudiant. Seul un administrateur peut consulter la liste complète des comptes.
                </Alert>
            )}

            <Modal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                title="Nouveau compte utilisateur"
                size="lg"
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="utilisateur-form" loading={createMutation.isPending}>
                            Créer le compte
                        </Button>
                    </>
                }
            >
                <form id="utilisateur-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="E-mail" required error={fieldErrors.email?.[0]}>
                            <Input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
                        </Field>
                        <Field label="Mot de passe" required hint="8 caractères minimum" error={fieldErrors.password?.[0]}>
                            <Input
                                type="password"
                                value={form.password}
                                onChange={(e) => setForm({ ...form, password: e.target.value })}
                                required
                            />
                        </Field>
                    </div>

                    <Field label="Rôle" required error={fieldErrors.role?.[0]}>
                        <Select value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })} required>
                            <option value="" disabled>
                                Sélectionner un rôle
                            </option>
                            {roleOptions.map((r) => (
                                <option key={r} value={r}>
                                    {ROLE_LABELS[r]}
                                </option>
                            ))}
                        </Select>
                    </Field>

                    {(form.role === ETUDIANT || form.role === ENSEIGNANT) && (
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Nom" required error={fieldErrors.nom?.[0]}>
                                <Input value={form.nom} onChange={(e) => setForm({ ...form, nom: e.target.value })} required />
                            </Field>
                            <Field label="Prénom" required error={fieldErrors.prenom?.[0]}>
                                <Input value={form.prenom} onChange={(e) => setForm({ ...form, prenom: e.target.value })} required />
                            </Field>
                        </div>
                    )}

                    {form.role === ETUDIANT && (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field label="Matricule" required error={fieldErrors.matricule?.[0]}>
                                    <Input value={form.matricule} onChange={(e) => setForm({ ...form, matricule: e.target.value })} required />
                                </Field>
                                <Field label="Date de naissance">
                                    <Input
                                        type="date"
                                        value={form.date_naissance}
                                        onChange={(e) => setForm({ ...form, date_naissance: e.target.value })}
                                    />
                                </Field>
                            </div>
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
                        </>
                    )}

                    {form.role === ENSEIGNANT && (
                        <Field label="Spécialité">
                            <Input value={form.specialite} onChange={(e) => setForm({ ...form, specialite: e.target.value })} />
                        </Field>
                    )}

                    <Checkbox
                        label="Compte actif"
                        checked={form.actif}
                        onChange={(e) => setForm({ ...form, actif: e.target.checked })}
                    />
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDeactivate}
                title="Désactiver le compte"
                message={`Confirmez-vous la désactivation du compte « ${toDeactivate?.email} » ? L'utilisateur sera immédiatement déconnecté.`}
                loading={desactiverMutation.isPending}
                onCancel={() => setToDeactivate(null)}
                onConfirm={confirmDeactivate}
            />
        </div>
    );
}

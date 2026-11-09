import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useEtudiant, useUpdateEtudiant } from '../features/etudiants';
import { useFilieres } from '../features/filieres';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { Alert, Button, Card, Field, Input, PageHeader, Select } from '../components/ui';

export default function EtudiantDetailPage() {
    const { id } = useParams();
    const { isAdmin, isScolarite } = useAuth();
    const canEdit = isAdmin || isScolarite;

    const { data, isLoading, error } = useEtudiant(id);
    const { data: filieresData } = useFilieres();
    const filieres = filieresData?.data ?? [];
    const updateMutation = useUpdateEtudiant();

    const etudiant = data?.data;
    const [form, setForm] = useState(null);
    const [formError, setFormError] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});
    const [success, setSuccess] = useState(false);

    useEffect(() => {
        if (etudiant) {
            setForm({
                nom: etudiant.nom,
                prenom: etudiant.prenom,
                matricule: etudiant.matricule,
                filiere_id: String(etudiant.filiere_id),
            });
        }
    }, [etudiant]);

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});
        setSuccess(false);
        try {
            await updateMutation.mutateAsync({
                id,
                payload: { ...form, filiere_id: Number(form.filiere_id) },
            });
            setSuccess(true);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer les modifications."));
            setFieldErrors(extractFieldErrors(err));
        }
    }

    if (isLoading || !form) return <Alert variant="info">Chargement…</Alert>;
    if (error) return <Alert variant="error">{extractErrorMessage(error, 'Étudiant introuvable.')}</Alert>;

    return (
        <div>
            <PageHeader
                title={`${etudiant.nom} ${etudiant.prenom}`}
                description={`Matricule ${etudiant.matricule}`}
                actions={
                    <Link to="/etudiants" className="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        ← Retour aux étudiants
                    </Link>
                }
            />

            <Card title="Dossier étudiant" className="max-w-xl">
                <form className="space-y-4" onSubmit={handleSubmit}>
                    {success && <Alert variant="success">Modifications enregistrées.</Alert>}
                    <Alert variant="error">{formError}</Alert>
                    <div className="grid grid-cols-2 gap-4">
                        <Field label="Nom" error={fieldErrors.nom?.[0]}>
                            <Input value={form.nom} disabled={!canEdit} onChange={(e) => setForm({ ...form, nom: e.target.value })} />
                        </Field>
                        <Field label="Prénom" error={fieldErrors.prenom?.[0]}>
                            <Input value={form.prenom} disabled={!canEdit} onChange={(e) => setForm({ ...form, prenom: e.target.value })} />
                        </Field>
                    </div>
                    <Field label="Matricule" error={fieldErrors.matricule?.[0]}>
                        <Input value={form.matricule} disabled={!canEdit} onChange={(e) => setForm({ ...form, matricule: e.target.value })} />
                    </Field>
                    <Field label="Filière" error={fieldErrors.filiere_id?.[0]}>
                        <Select value={form.filiere_id} disabled={!canEdit} required onChange={(e) => setForm({ ...form, filiere_id: e.target.value })}>
                            {filieres.map((f) => (
                                <option key={f.id} value={f.id}>
                                    {f.code} — {f.nom}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    {canEdit && (
                        <Button type="submit" loading={updateMutation.isPending}>
                            Enregistrer
                        </Button>
                    )}
                </form>
            </Card>
        </div>
    );
}

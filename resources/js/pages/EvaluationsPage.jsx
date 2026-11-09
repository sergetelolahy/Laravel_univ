import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useEvaluations, useCreateEvaluation, useSaisirNotes } from '../features/evaluations';
import { useCoursList, useInscritsDuCours } from '../features/cours';
import { useAnnees, useAnneeCourante } from '../features/annees';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { formatDate, formatNumber } from '../lib/format';
import { Alert, Button, Card, Field, Input, Modal, PageHeader, Select } from '../components/ui';

export default function EvaluationsPage() {
    const { isEnseignant } = useAuth();
    const { data: coursData } = useCoursList();
    const { data: anneesData } = useAnnees();
    const { data: anneeCouranteData } = useAnneeCourante();
    const coursListe = coursData?.data ?? [];
    const annees = anneesData?.data ?? [];

    const [coursFiltre, setCoursFiltre] = useState('');
    const [page, setPage] = useState(1);
    const params = { page, per_page: 15, ...(coursFiltre && { cours_id: coursFiltre }) };
    const { data, isLoading, error } = useEvaluations(params);

    const createMutation = useCreateEvaluation();

    const [modalOpen, setModalOpen] = useState(false);
    const [form, setForm] = useState({ cours_id: '', annee_id: '', type: '', ponderation: '', date_eval: '' });
    const [formError, setFormError] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});
    const [notesEvaluation, setNotesEvaluation] = useState(null);

    const pagination = data?.data;
    const rows = pagination?.data ?? [];

    function openCreate() {
        setForm({
            cours_id: '',
            annee_id: anneeCouranteData?.data ? String(anneeCouranteData.data.id) : '',
            type: '',
            ponderation: '',
            date_eval: '',
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
            annee_id: Number(form.annee_id),
            type: form.type,
            ponderation: Number(form.ponderation),
            ...(form.date_eval && { date_eval: form.date_eval }),
        };
        try {
            await createMutation.mutateAsync(payload);
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible de créer l'évaluation."));
            setFieldErrors(extractFieldErrors(err));
        }
    }

    return (
        <div>
            <PageHeader
                title="Évaluations & notes"
                description="Créer des évaluations et saisir les notes des étudiants."
                actions={<Button onClick={openCreate}>Nouvelle évaluation</Button>}
            />

            <Card
                title="Liste des évaluations"
                actions={
                    <Select value={coursFiltre} onChange={(e) => { setCoursFiltre(e.target.value); setPage(1); }} className="w-full sm:w-56">
                        <option value="">Tous les cours</option>
                        {coursListe.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.code}
                            </option>
                        ))}
                    </Select>
                }
            >
                {formError && !modalOpen && <Alert variant="error" className="mb-4">{formError}</Alert>}
                {isLoading && <p className="py-8 text-center text-sm text-slate-500">Chargement des évaluations…</p>}
                {error && <Alert variant="error">{extractErrorMessage(error, 'Impossible de charger les évaluations.')}</Alert>}
                {!isLoading && !error && rows.length === 0 && (
                    <p className="py-8 text-center text-sm text-slate-500">Aucune évaluation trouvée.</p>
                )}
                {!isLoading && !error && rows.length > 0 && (
                    <div className="-mx-5 overflow-x-auto px-5">
                        <table className="min-w-[42rem] w-full divide-y divide-slate-100 text-sm">
                            <thead>
                                <tr className="text-left text-xs font-bold uppercase tracking-wide text-slate-400">
                                    <th scope="col" className="px-3 py-3">Cours</th>
                                    <th scope="col" className="px-3 py-3">Type</th>
                                    <th scope="col" className="px-3 py-3">Pondération</th>
                                    <th scope="col" className="px-3 py-3">Date</th>
                                    {isEnseignant && <th scope="col" className="px-3 py-3 text-right">Actions</th>}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {rows.map((evaluation) => {
                                    const cours = coursListe.find((c) => c.id === evaluation.cours_id);
                                    return (
                                        <tr key={evaluation.id} className="transition hover:bg-slate-50">
                                            <td className="px-3 py-3 font-semibold text-slate-800">{cours?.code ?? 'Cours indisponible'}</td>
                                            <td className="px-3 py-3 text-slate-600">{evaluation.type}</td>
                                            <td className="px-3 py-3 text-slate-600">{formatNumber(evaluation.ponderation)}</td>
                                            <td className="px-3 py-3 text-slate-600">{formatDate(evaluation.date_eval)}</td>
                                            {isEnseignant && (
                                                <td className="px-3 py-3 text-right">
                                                    <Button variant="ghost" onClick={() => setNotesEvaluation(evaluation)}>
                                                        Saisir les notes
                                                    </Button>
                                                </td>
                                            )}
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}

                {pagination && pagination.last_page > 1 && (
                    <div className="mt-4 flex items-center justify-between text-sm text-slate-600">
                        <Button variant="secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
                            Précédent
                        </Button>
                        <span>
                            Page {pagination.current_page} / {pagination.last_page}
                        </span>
                        <Button variant="secondary" disabled={page >= pagination.last_page} onClick={() => setPage((p) => p + 1)}>
                            Suivant
                        </Button>
                    </div>
                )}
            </Card>

            <Modal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                title="Nouvelle évaluation"
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="evaluation-form" loading={createMutation.isPending}>
                            Créer
                        </Button>
                    </>
                }
            >
                <form id="evaluation-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
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
                    <Field label="Type" required hint="Ex. Examen, Contrôle, TP" error={fieldErrors.type?.[0]}>
                        <Input value={form.type} maxLength={30} onChange={(e) => setForm({ ...form, type: e.target.value })} required />
                    </Field>
                    <Field label="Pondération" required error={fieldErrors.ponderation?.[0]}>
                        <Input
                            type="number"
                            step="0.01"
                            min="0.01"
                            value={form.ponderation}
                            onChange={(e) => setForm({ ...form, ponderation: e.target.value })}
                            required
                        />
                    </Field>
                    <Field label="Date de l'évaluation" error={fieldErrors.date_eval?.[0]}>
                        <Input type="date" value={form.date_eval} onChange={(e) => setForm({ ...form, date_eval: e.target.value })} />
                    </Field>
                </form>
            </Modal>

            {notesEvaluation && <SaisieNotesModal evaluation={notesEvaluation} onClose={() => setNotesEvaluation(null)} />}
        </div>
    );
}

function SaisieNotesModal({ evaluation, onClose }) {
    const inscritsQuery = useInscritsDuCours(evaluation.cours_id, { annee_id: evaluation.annee_id });
    const saisirMutation = useSaisirNotes(evaluation.id);
    const [valeurs, setValeurs] = useState({});
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    const inscrits = inscritsQuery.data?.data ?? [];

    async function handleSubmit(event) {
        event.preventDefault();
        setError('');
        setSuccess(false);
        const notes = Object.entries(valeurs)
            .filter(([, v]) => v !== '' && v !== undefined)
            .map(([etudiant_id, valeur]) => ({ etudiant_id: Number(etudiant_id), valeur: Number(valeur) }));

        if (notes.length === 0) {
            setError('Saisissez au moins une note.');
            return;
        }

        try {
            await saisirMutation.mutateAsync(notes);
            setSuccess(true);
        } catch (err) {
            setError(extractErrorMessage(err, "Impossible d'enregistrer les notes."));
        }
    }

    return (
        <Modal
            open
            onClose={onClose}
            title={`Saisie des notes — ${evaluation.type}`}
            size="lg"
            footer={
                <>
                    <Button variant="secondary" onClick={onClose}>
                        Fermer
                    </Button>
                    <Button type="submit" form="notes-form" loading={saisirMutation.isPending}>
                        Enregistrer les notes
                    </Button>
                </>
            }
        >
            <form id="notes-form" onSubmit={handleSubmit} className="space-y-4">
                {success && <Alert variant="success">Notes enregistrées avec succès.</Alert>}
                <Alert variant="error">{error}</Alert>
                {inscritsQuery.isLoading && <Alert variant="info">Chargement des inscrits…</Alert>}
                {inscritsQuery.error && <Alert variant="error">{extractErrorMessage(inscritsQuery.error)}</Alert>}
                {!inscritsQuery.isLoading && inscrits.length === 0 && (
                    <Alert variant="warning">Aucun étudiant inscrit à ce cours pour cette année.</Alert>
                )}
                <div className="space-y-2">
                    {inscrits.map((i) => (
                        <div key={i.etudiant_id} className="flex items-center justify-between gap-3">
                            <span className="text-sm text-slate-700">
                                <span className="font-semibold">{i.etudiant?.matricule ?? 'Matricule indisponible'}</span>
                                {i.etudiant?.nom && ` — ${i.etudiant.nom} ${i.etudiant.prenom ?? ''}`.trim()}
                            </span>
                            <Input
                                type="number"
                                min="0"
                                max="20"
                                step="0.25"
                                className="w-28"
                                value={valeurs[i.etudiant_id] ?? ''}
                                onChange={(e) => setValeurs({ ...valeurs, [i.etudiant_id]: e.target.value })}
                                placeholder="/ 20"
                            />
                        </div>
                    ))}
                </div>
            </form>
        </Modal>
    );
}

import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useInscriptions, useCreateInscription, useCreateInscriptionsLot, useRemoveInscription } from '../features/inscriptions';
import { useCoursList } from '../features/cours';
import { useEtudiants } from '../features/etudiants';
import { useAnnees, useAnneeCourante } from '../features/annees';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { formatDateTime } from '../lib/format';
import { Alert, Button, Card, ConfirmDialog, DataTable, Field, Modal, PageHeader, Select, Textarea } from '../components/ui';

function grouperCoursParSemestre(cours) {
    return cours.reduce((groupes, item) => {
        const semestre = item.semestre?.numero ?? 'Non défini';
        groupes[semestre] ??= [];
        groupes[semestre].push(item);
        return groupes;
    }, {});
}

export default function InscriptionsPage() {
    const { isAdmin, isScolarite } = useAuth();
    const canWrite = isAdmin || isScolarite;

    const { data: coursData } = useCoursList({ per_page: 100 });
    const { data: etudiantsData, isLoading: etudiantsLoading, error: etudiantsError } = useEtudiants({ per_page: 100 });
    const { data: anneesData } = useAnnees();
    const { data: anneeCouranteData } = useAnneeCourante();
    const coursListe = coursData?.data ?? [];
    const etudiants = etudiantsData?.data ?? [];
    const annees = anneesData?.data ?? [];

    const [coursFiltre, setCoursFiltre] = useState('');
    const [anneeFiltre, setAnneeFiltre] = useState('');
    const [page, setPage] = useState(1);

    const params = { page, ...(coursFiltre && { cours_id: coursFiltre }), ...(anneeFiltre && { annee_id: anneeFiltre }) };
    const { data, isLoading, error } = useInscriptions(params);

    const createMutation = useCreateInscription();
    const lotMutation = useCreateInscriptionsLot();
    const removeMutation = useRemoveInscription();

    const [modalOpen, setModalOpen] = useState(false);
    const [lotModalOpen, setLotModalOpen] = useState(false);
    const [form, setForm] = useState({ etudiant_id: '', cours_id: '', annee_id: '' });
    const [lotForm, setLotForm] = useState({ cours_id: '', annee_id: '', matricules: '' });
    const [formError, setFormError] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});
    const [toDelete, setToDelete] = useState(null);

    const rows = data?.data ?? [];
    const etudiantSelectionne = etudiants.find((etudiant) => String(etudiant.id) === form.etudiant_id);
    const coursDuProgramme = etudiantSelectionne?.filiere_id
        ? coursListe.filter((cours) => cours.semestre?.filiere?.id === etudiantSelectionne.filiere_id)
        : [];
    const coursParSemestre = grouperCoursParSemestre(coursDuProgramme);
    const coursLotParSemestre = grouperCoursParSemestre(coursListe);

    function openCreate() {
        setForm({ etudiant_id: '', cours_id: '', annee_id: anneeCouranteData?.data ? String(anneeCouranteData.data.id) : '' });
        setFormError('');
        setFieldErrors({});
        setModalOpen(true);
    }

    function openLot() {
        setLotForm({ cours_id: '', annee_id: anneeCouranteData?.data ? String(anneeCouranteData.data.id) : '', matricules: '' });
        setFormError('');
        setFieldErrors({});
        setLotModalOpen(true);
    }

    async function handleLotSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});
        const matricules = lotForm.matricules
            .split(/[\n,]/)
            .map((v) => v.trim())
            .filter(Boolean);

        if (matricules.length === 0) {
            setFormError('Indiquez au moins un matricule étudiant.');
            return;
        }

        const etudiantsParMatricule = new Map(etudiants.map((etudiant) => [etudiant.matricule.trim().toUpperCase(), etudiant]));
        const matriculesNormalises = matricules.map((matricule) => matricule.toUpperCase());
        const matriculesInconnus = [...new Set(matriculesNormalises.filter((matricule) => !etudiantsParMatricule.has(matricule)))];
        if (matriculesInconnus.length > 0) {
            setFormError(`Matricule(s) introuvable(s) : ${matriculesInconnus.join(', ')}.`);
            return;
        }

        const doublons = [...new Set(matriculesNormalises.filter((matricule, index) => matriculesNormalises.indexOf(matricule) !== index))];
        if (doublons.length > 0) {
            setFormError(`Matricule(s) répété(s) : ${doublons.join(', ')}.`);
            return;
        }

        const inscriptions = matriculesNormalises.map((matricule) => ({
            etudiant_id: etudiantsParMatricule.get(matricule).id,
            cours_id: Number(lotForm.cours_id),
            annee_id: Number(lotForm.annee_id),
        }));

        try {
            await lotMutation.mutateAsync(inscriptions);
            setLotModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible de créer les inscriptions en lot."));
            setFieldErrors(extractFieldErrors(err));
        }
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setFormError('');
        setFieldErrors({});
        const payload = {
            etudiant_id: Number(form.etudiant_id),
            cours_id: Number(form.cours_id),
            annee_id: Number(form.annee_id),
        };
        try {
            await createMutation.mutateAsync(payload);
            setModalOpen(false);
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible de créer l'inscription."));
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

    return (
        <div>
            <PageHeader
                title="Inscriptions"
                description="Inscriptions des étudiants aux cours."
                actions={
                    canWrite && (
                        <div className="flex gap-2">
                            <Button variant="secondary" onClick={openLot}>
                                Inscription en lot
                            </Button>
                            <Button onClick={openCreate}>Nouvelle inscription</Button>
                        </div>
                    )
                }
            />

            <Card
                actions={
                    <div className="flex gap-2">
                        <Select value={coursFiltre} onChange={(e) => { setCoursFiltre(e.target.value); setPage(1); }} className="w-48">
                            <option value="">Tous les cours</option>
                            {coursListe.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.code}
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
                            key: 'etudiant',
                            header: 'Étudiant',
                            render: (r) => r.etudiant
                                ? `${r.etudiant.matricule} — ${r.etudiant.nom} ${r.etudiant.prenom}`
                                : 'Étudiant indisponible',
                        },
                        {
                            key: 'cours',
                            header: 'Cours',
                            render: (r) => {
                                const cours = coursListe.find((item) => item.id === r.cours_id);
                                return cours ? `${cours.code} — ${cours.intitule}` : 'Cours indisponible';
                            },
                        },
                        { key: 'semestre', header: 'Semestre', render: (r) => coursListe.find((c) => c.id === r.cours_id)?.semestre?.numero ?? '—' },
                        { key: 'date_inscription', header: 'Date', render: (r) => formatDateTime(r.date_inscription) },
                    ]}
                    actions={
                        canWrite
                            ? (row) => (
                                  <Button variant="ghost" onClick={() => setToDelete(row)}>
                                      Supprimer
                                  </Button>
                              )
                            : undefined
                    }
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
                title="Nouvelle inscription"
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="inscription-form" loading={createMutation.isPending}>
                            Inscrire
                        </Button>
                    </>
                }
            >
                <form id="inscription-form" className="space-y-4" onSubmit={handleSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <Field label="Matricule de l'étudiant" required error={fieldErrors.etudiant_id?.[0]}>
                        <Select
                            value={form.etudiant_id}
                            onChange={(e) => setForm({ ...form, etudiant_id: e.target.value, cours_id: '' })}
                            disabled={etudiantsLoading}
                            required
                        >
                            <option value="" disabled>
                                {etudiantsLoading ? 'Chargement des étudiants…' : 'Sélectionner un matricule…'}
                            </option>
                            {etudiants.map((etudiant) => (
                                <option key={etudiant.id} value={etudiant.id}>
                                    {etudiant.matricule} — {etudiant.nom} {etudiant.prenom}
                                </option>
                            ))}
                        </Select>
                    </Field>
                    {etudiantsError && <Alert variant="error">{extractErrorMessage(etudiantsError, 'Impossible de charger les étudiants.')}</Alert>}
                    {etudiantSelectionne && (
                        <Alert variant={etudiantSelectionne.filiere_id ? 'info' : 'error'}>
                            {etudiantSelectionne.filiere
                                ? `${etudiantSelectionne.nom} ${etudiantSelectionne.prenom} · Filière ${etudiantSelectionne.filiere.code} — ${etudiantSelectionne.filiere.nom}`
                                : 'Cet étudiant n’est affecté à aucune filière. Affectez-le à une filière avant son inscription.'}
                        </Alert>
                    )}
                    <Field label="Cours" required error={fieldErrors.cours_id?.[0]}>
                        <Select value={form.cours_id} disabled={!etudiantSelectionne?.filiere_id} onChange={(e) => setForm({ ...form, cours_id: e.target.value })} required>
                            <option value="" disabled>
                                {etudiantSelectionne?.filiere_id ? 'Sélectionner un cours…' : 'Choisir d’abord un étudiant avec une filière…'}
                            </option>
                            {Object.entries(coursParSemestre).map(([semestre, cours]) => (
                                <optgroup key={semestre} label={`Semestre ${semestre}`}>
                                    {cours.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.code} — {c.intitule}
                                        </option>
                                    ))}
                                </optgroup>
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
                </form>
            </Modal>

            <Modal
                open={lotModalOpen}
                onClose={() => setLotModalOpen(false)}
                title="Inscription en lot"
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setLotModalOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit" form="inscription-lot-form" loading={lotMutation.isPending}>
                            Inscrire tous les étudiants
                        </Button>
                    </>
                }
            >
                <form id="inscription-lot-form" className="space-y-4" onSubmit={handleLotSubmit}>
                    <Alert variant="error">{formError}</Alert>
                    <Field label="Cours" required>
                        <Select value={lotForm.cours_id} onChange={(e) => setLotForm({ ...lotForm, cours_id: e.target.value })} required>
                            <option value="" disabled>
                                Sélectionner…
                            </option>
                            {Object.entries(coursLotParSemestre).map(([semestre, cours]) => (
                                <optgroup key={semestre} label={`Semestre ${semestre}`}>
                                    {cours.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.code} — {c.intitule}
                                        </option>
                                    ))}
                                </optgroup>
                            ))}
                        </Select>
                    </Field>
                    <Field label="Année universitaire" required>
                        <Select value={lotForm.annee_id} onChange={(e) => setLotForm({ ...lotForm, annee_id: e.target.value })} required>
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
                    <Field label="Matricules des étudiants" required hint="Un matricule par ligne ou séparés par des virgules">
                        <Textarea
                            rows={5}
                            value={lotForm.matricules}
                            onChange={(e) => setLotForm({ ...lotForm, matricules: e.target.value })}
                            placeholder={'ET0001\nET0002\nET0003'}
                            required
                        />
                    </Field>
                </form>
            </Modal>

            <ConfirmDialog
                open={!!toDelete}
                title="Supprimer l'inscription"
                message="Confirmez-vous la suppression de cette inscription ?"
                loading={removeMutation.isPending}
                onCancel={() => setToDelete(null)}
                onConfirm={confirmDelete}
            />
        </div>
    );
}

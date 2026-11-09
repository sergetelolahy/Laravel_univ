import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useAnneeCourante } from '../features/annees';
import { useCoursList } from '../features/cours';
import { useFilieres } from '../features/filieres';
import { useGroupes } from '../features/groupes';
import {
    useAjouterSeancePlanning,
    useCreerPlanning,
    useDisponibilites,
    useEnregistrerDisponibilites,
    usePlanning,
    usePlannings,
    usePublierPlanning,
    useSoumettrePlanning,
    useSupprimerSeancePlanning,
    useValiderPlanning,
} from '../features/plannings';
import { useSalles } from '../features/salles';
import { useEnseignants } from '../features/enseignants';
import { extractErrorMessage, extractFieldErrors } from '../lib/api';
import { ADMIN, SCOLARITE } from '../lib/roles';
import { Alert, Badge, Button, Card, Field, Input, PageHeader, Select, Spinner } from '../components/ui';

function lundiDe(date = new Date()) {
    const lundi = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const jour = lundi.getDay();
    lundi.setDate(lundi.getDate() + (jour === 0 ? -6 : 1 - jour));
    return `${lundi.getFullYear()}-${String(lundi.getMonth() + 1).padStart(2, '0')}-${String(lundi.getDate()).padStart(2, '0')}`;
}

function deplacerSemaine(date, nombre) {
    const value = new Date(`${date}T12:00:00`);
    value.setDate(value.getDate() + 7 * nombre);
    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, '0')}-${String(value.getDate()).padStart(2, '0')}`;
}

function dateFinSemaine(date) {
    const value = new Date(`${deplacerSemaine(date, 1)}T12:00:00`);
    value.setDate(value.getDate() - 1);
    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, '0')}-${String(value.getDate()).padStart(2, '0')}`;
}

function erreurDisponibilites(semaine, creneaux) {
    const semaineSuivante = deplacerSemaine(semaine, 1);
    const plages = [];

    for (const [index, creneau] of creneaux.entries()) {
        if (!creneau.debut || !creneau.fin) {
            return `Complète les heures du créneau ${index + 1}.`;
        }

        const jourDebut = creneau.debut.slice(0, 10);
        const jourFin = creneau.fin.slice(0, 10);
        if (jourDebut < semaine || jourDebut >= semaineSuivante || jourFin < semaine || jourFin >= semaineSuivante) {
            return `Le créneau ${index + 1} doit être compris entre le ${semaine} et le ${dateFinSemaine(semaine)}.`;
        }
        if (jourDebut !== jourFin) {
            return `Le créneau ${index + 1} doit commencer et finir le même jour.`;
        }
        if (creneau.fin <= creneau.debut) {
            return `L’heure de fin du créneau ${index + 1} doit être après son heure de début.`;
        }

        const dureeMinutes = (new Date(creneau.fin).getTime() - new Date(creneau.debut).getTime()) / 60000;
        if (dureeMinutes > 4 * 60) {
            return `Le créneau ${index + 1} ne peut pas dépasser quatre heures.`;
        }

        plages.push({ debut: creneau.debut, fin: creneau.fin, index: index + 1 });
    }

    plages.sort((a, b) => a.debut.localeCompare(b.debut));
    for (let index = 1; index < plages.length; index++) {
        if (plages[index].debut < plages[index - 1].fin) {
            return `Les créneaux ${plages[index - 1].index} et ${plages[index].index} se chevauchent.`;
        }
    }

    return '';
}

function dateHeureLocale(value) {
    return value ? value.replace(' ', 'T').slice(0, 16) : '';
}

function dateHeureApi(value) {
    return value ? `${value.replace('T', ' ')}:00` : '';
}

function semaineLisible(date) {
    const fin = deplacerSemaine(date, 1);
    const dernierJour = new Date(`${fin}T12:00:00`);
    dernierJour.setDate(dernierJour.getDate() - 1);
    return `${new Date(`${date}T12:00:00`).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' })} – ${dernierJour.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' })}`;
}

function dateHeureDisponibilite(value) {
    const [date, heure] = value.split(' ');
    const [annee, mois, jour] = date.split('-').map(Number);
    const jourLisible = new Date(annee, mois - 1, jour).toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });

    return `${jourLisible} · ${heure.slice(0, 5)}`;
}

const STATUTS = {
    PREPARATION: { label: 'En préparation', variant: 'amber' },
    A_VALIDER: { label: 'À valider par la scolarité', variant: 'indigo' },
    VALIDEE: { label: 'Validé · à publier', variant: 'green' },
    PUBLIEE: { label: 'Publié', variant: 'green' },
};

function StatutPlanning({ statut }) {
    const config = STATUTS[statut] ?? { label: statut, variant: 'slate' };
    return <Badge variant={config.variant}>{config.label}</Badge>;
}

export default function PlanningHebdomadairePage() {
    const { role } = useAuth();
    const [semaine, setSemaine] = useState(lundiDe);
    const { data: anneeCourante } = useAnneeCourante();
    const teacher = role !== ADMIN && role !== SCOLARITE;

    return (
        <div>
            <PageHeader
                title={teacher ? 'Mes disponibilités' : 'EDT hebdomadaires'}
                description={
                    teacher
                        ? 'Déclare tes créneaux libres afin que la scolarité organise les cours chaque semaine.'
                        : 'Coordonnez les disponibilités, la préparation, la validation et la publication des emplois du temps.'
                }
                actions={<Badge variant={role === ADMIN ? 'indigo' : role === SCOLARITE ? 'amber' : 'green'}>{teacher ? 'Enseignant' : role === SCOLARITE ? 'Scolarité' : 'Administration'}</Badge>}
            />

            <Card className="mb-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-sm font-bold text-slate-900">Semaine concernée</p>
                        <p className="mt-1 text-sm text-slate-500">{semaineLisible(semaine)} · Année {anneeCourante?.data?.libelle ?? 'courante'}</p>
                    </div>
                    <div className="flex flex-wrap items-end gap-2">
                        <Button variant="secondary" onClick={() => setSemaine((value) => deplacerSemaine(value, -1))} aria-label="Semaine précédente">
                            ← <span className="hidden sm:inline">Précédente</span>
                        </Button>
                        <Field label="Lundi">
                            <Input type="date" value={semaine} onChange={(event) => setSemaine(lundiDe(new Date(`${event.target.value}T12:00:00`)))} />
                        </Field>
                        <Button variant="secondary" onClick={() => setSemaine((value) => deplacerSemaine(value, 1))} aria-label="Semaine suivante">
                            <span className="hidden sm:inline">Suivante</span> →
                        </Button>
                    </div>
                </div>
            </Card>

            {teacher ? <DisponibilitesEnseignant key={semaine} semaine={semaine} anneeId={anneeCourante?.data?.id} /> : (
                <GestionPlanning key={semaine} semaine={semaine} role={role} />
            )}
        </div>
    );
}

function DisponibilitesEnseignant({ semaine, anneeId }) {
    const { data, isLoading, error } = useDisponibilites(semaine);
    const saveMutation = useEnregistrerDisponibilites();
    const [creneaux, setCreneaux] = useState([]);
    const [messageErreur, setMessageErreur] = useState('');
    const disponibilites = data?.data ?? [];

    useEffect(() => {
        setCreneaux(
            disponibilites.map((item) => ({
                debut: dateHeureLocale(item.debut),
                fin: dateHeureLocale(item.fin),
                note: item.note ?? '',
            }))
        );
    }, [data, semaine]);

    function ajouterCreneau() {
        setCreneaux((items) => [...items, { debut: `${semaine}T08:00`, fin: `${semaine}T10:00`, note: '' }]);
    }

    function modifierCreneau(index, key, value) {
        setCreneaux((items) => items.map((item, current) => (current === index ? { ...item, [key]: value } : item)));
    }

    async function enregistrer(event) {
        event.preventDefault();
        setMessageErreur('');
        const erreurLocale = erreurDisponibilites(semaine, creneaux);
        if (erreurLocale) {
            saveMutation.reset();
            setMessageErreur(erreurLocale);
            return;
        }

        try {
            await saveMutation.mutateAsync({
                semaine_debut: semaine,
                disponibilites: creneaux.map((item) => ({
                    debut: dateHeureApi(item.debut),
                    fin: dateHeureApi(item.fin),
                    ...(item.note && { note: item.note }),
                })),
            });
        } catch (requestError) {
            setMessageErreur(extractErrorMessage(requestError, 'Impossible d’enregistrer vos disponibilités.'));
        }
    }

    return (
        <div className="space-y-5">
            <Card title="Déclarer mes plages disponibles">
                {error && <Alert variant="error">{extractErrorMessage(error, 'Impossible de charger vos disponibilités.')}</Alert>}
                {messageErreur && <Alert variant="error" className="mb-4">{messageErreur}</Alert>}
                {isLoading ? <Spinner label="Chargement de vos disponibilités…" /> : (
                    <form onSubmit={enregistrer} className="space-y-4">
                        <p className="text-sm leading-6 text-slate-500">
                            Ajoute les jours et heures auxquels tu peux assurer tes cours. Tu peux modifier tes disponibilités tant que la proposition d’EDT n’a pas été soumise.
                        </p>
                        {creneaux.length === 0 && (
                            <p className="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                                Aucune disponibilité renseignée pour cette semaine.
                            </p>
                        )}
                        {creneaux.map((item, index) => (
                            <div key={`${index}-${semaine}`} className="grid gap-3 rounded-xl border border-slate-100 p-4 sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-end">
                                <Field label="Disponible à partir de" hint={`Du ${semaine} au ${dateFinSemaine(semaine)}`}>
                                    <Input
                                        type="datetime-local"
                                        min={`${semaine}T00:00`}
                                        max={`${dateFinSemaine(semaine)}T23:59`}
                                        value={item.debut}
                                        onChange={(event) => modifierCreneau(index, 'debut', event.target.value)}
                                        required
                                    />
                                </Field>
                                <Field label="Disponible jusqu’à" hint={`Du ${semaine} au ${dateFinSemaine(semaine)}`}>
                                    <Input
                                        type="datetime-local"
                                        min={`${semaine}T00:00`}
                                        max={`${dateFinSemaine(semaine)}T23:59`}
                                        value={item.fin}
                                        onChange={(event) => modifierCreneau(index, 'fin', event.target.value)}
                                        required
                                    />
                                </Field>
                                <Field label="Précision (facultatif)">
                                    <Input
                                        value={item.note}
                                        maxLength={255}
                                        onChange={(event) => modifierCreneau(index, 'note', event.target.value)}
                                        placeholder="Ex. cours à distance"
                                    />
                                </Field>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    aria-label={`Supprimer le créneau ${index + 1}`}
                                    onClick={() => setCreneaux((items) => items.filter((_, current) => current !== index))}
                                >
                                    Retirer
                                </Button>
                            </div>
                        ))}
                        <div className="flex flex-wrap gap-2">
                            <Button type="button" variant="secondary" onClick={ajouterCreneau}>+ Ajouter un créneau</Button>
                            <Button type="submit" loading={saveMutation.isPending} disabled={!anneeId || creneaux.length === 0}>
                                Enregistrer mes disponibilités
                            </Button>
                        </div>
                    </form>
                )}
            </Card>
            {saveMutation.isSuccess && (
                <Alert variant="success">Tes disponibilités pour cette semaine sont enregistrées et accessibles à la scolarité.</Alert>
            )}
        </div>
    );
}

function GestionPlanning({ semaine, role }) {
    const isAdmin = role === ADMIN;
    const [filiereId, setFiliereId] = useState('');
    const [planningId, setPlanningId] = useState('');
    const [erreur, setErreur] = useState('');
    const [success, setSuccess] = useState('');
    const filieresQuery = useFilieres({ per_page: 100 });
    const plansQuery = usePlannings({ semaine_debut: semaine });
    const planningIds = (plansQuery.data?.data ?? []).filter((planning) => {
        const parcoursCorrespond = !filiereId || String(planning.filiere_id) === filiereId;
        const statutVisible = !isAdmin || ['VALIDEE', 'PUBLIEE'].includes(planning.statut);
        return parcoursCorrespond && statutVisible;
    });
    const planningQuery = usePlanning(planningId);
    const disponibilitesQuery = useDisponibilites(semaine);
    const disponibilites = disponibilitesQuery.data?.data ?? [];
    const { data: coursData } = useCoursList({ per_page: 100 });
    const { data: groupesData } = useGroupes({ per_page: 100 });
    const { data: enseignantsData } = useEnseignants({ per_page: 100 });
    const { data: sallesData } = useSalles({ per_page: 100 });

    const createMutation = useCreerPlanning({
        onSuccess: (response) => {
            setPlanningId(String(response.data.id));
            setErreur('');
            setSuccess('Emploi du temps créé. Tu peux maintenant ajouter des séances.');
        },
    });
    const addSessionMutation = useAjouterSeancePlanning({
        onSuccess: () => {
            setErreur('');
            setSuccess('Séance ajoutée à la proposition.');
    }});
    const deleteSessionMutation = useSupprimerSeancePlanning();
    const submitMutation = useSoumettrePlanning({
        onSuccess: () => setSuccess('Emploi du temps soumis à validation.'),
    });
    const validateMutation = useValiderPlanning({
        onSuccess: () => setSuccess('Emploi du temps validé. Il attend la publication de l’administrateur.'),
    });
    const publishMutation = usePublierPlanning({
        onSuccess: () => setSuccess('L’EDT hebdomadaire est publié et visible dans les emplois du temps personnels.'),
    });
    const planning = planningQuery.data?.data;

    useEffect(() => {
        if (!planningId && planningIds.length > 0) {
            setPlanningId(String(planningIds[0].id));
            setFiliereId(String(planningIds[0].filiere_id));
        }
    }, [planningIds, planningId]);

    const filieres = filieresQuery.data?.data ?? [];

    function choisirFiliere(value) {
        setFiliereId(value);
        const existing = (plansQuery.data?.data ?? []).find((item) => String(item.filiere_id) === value);
        setPlanningId(existing ? String(existing.id) : '');
        setErreur('');
        setSuccess('');
    }

    async function creer() {
        setErreur('');
        setSuccess('');
        try {
            await createMutation.mutateAsync({ filiere_id: Number(filiereId), semaine_debut: semaine });
        } catch (requestError) {
            setErreur(extractErrorMessage(requestError, 'Impossible de créer cet emploi du temps.'));
        }
    }

    async function executerAction(mutation, payload = { id: planningId }) {
        setErreur('');
        setSuccess('');
        try {
            await mutation.mutateAsync(payload);
        } catch (requestError) {
            setErreur(extractErrorMessage(requestError, 'Action impossible.'));
        }
    }

    const optionsCours = useMemo(
        () =>
            (coursData?.data ?? []).filter(
                (cours) => String(cours.semestre?.filiere?.id ?? '') === String(planning?.filiere_id ?? filiereId)
            ),
        [coursData, filiereId, planning?.filiere_id]
    );

    return (
        <div className="space-y-5">
            {erreur && <Alert variant="error">{erreur}</Alert>}
            {success && <Alert variant="success">{success}</Alert>}

            <Card title={isAdmin ? 'Emplois à publier' : 'Choisir un parcours'}>
                {!isAdmin && (
                    <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div className="flex-1">
                            <Field label="Parcours">
                                <Select value={filiereId} onChange={(event) => choisirFiliere(event.target.value)}>
                                    <option value="">Choisir un parcours…</option>
                                    {filieres.map((filiere) => (
                                        <option key={filiere.id} value={filiere.id}>{filiere.code} — {filiere.nom}</option>
                                    ))}
                                </Select>
                            </Field>
                        </div>
                        {!planningIds.some((item) => String(item.filiere_id) === filiereId) && filiereId && (
                            <Button onClick={creer} loading={createMutation.isPending}>Ouvrir la préparation</Button>
                        )}
                    </div>
                )}
                {plansQuery.isLoading && <Spinner label="Chargement des emplois du temps…" />}
                {plansQuery.error && <Alert variant="error">{extractErrorMessage(plansQuery.error, 'Impossible de charger les propositions.')}</Alert>}
                {!plansQuery.isLoading && planningIds.length === 0 && (
                    <p className="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                        {isAdmin ? 'Aucun emploi du temps en attente pour cette semaine.' : 'Aucune proposition ouverte pour ce parcours et cette semaine.'}
                    </p>
                )}
                {planningIds.length > 0 && (
                    <div className="space-y-2">
                        {planningIds.map((item) => (
                            <button
                                key={item.id}
                                type="button"
                                onClick={() => {
                                    setPlanningId(String(item.id));
                                    setFiliereId(String(item.filiere_id));
                                    setSuccess('');
                                    setErreur('');
                                }}
                                className={`flex w-full flex-wrap items-center justify-between gap-3 rounded-xl border p-4 text-left transition ${
                                    String(item.id) === planningId ? 'border-indigo-200 bg-indigo-50/60' : 'border-slate-100 hover:bg-slate-50'
                                }`}
                            >
                                <span>
                                    <span className="block font-bold text-slate-900">{item.filiere_code} · {item.filiere_nom}</span>
                                    <span className="mt-1 block text-xs text-slate-500">{item.annee} · semaine du {item.semaine_debut}</span>
                                </span>
                                <StatutPlanning statut={item.statut} />
                            </button>
                        ))}
                    </div>
                )}
            </Card>

            {role === SCOLARITE && (
                <Card
                    title="Disponibilités enseignantes reçues"
                    actions={
                        <div className="flex items-center gap-3">
                            <Badge variant="indigo">{disponibilites.length} créneau{disponibilites.length > 1 ? 'x' : ''}</Badge>
                            <Button variant="secondary" onClick={() => disponibilitesQuery.refetch()} loading={disponibilitesQuery.isFetching}>
                                Actualiser
                            </Button>
                        </div>
                    }
                >
                    {disponibilitesQuery.error ? (
                        <Alert variant="error">{extractErrorMessage(disponibilitesQuery.error, 'Impossible de récupérer les disponibilités enseignantes.')}</Alert>
                    ) : disponibilitesQuery.isLoading ? (
                        <Spinner label="Récupération des disponibilités enseignantes…" />
                    ) : disponibilites.length === 0 ? (
                        <p className="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                            Aucune disponibilité reçue pour la semaine du {semaine}. Vérifiez que les enseignants ont enregistré leurs créneaux pour cette même semaine.
                        </p>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {disponibilites.map((item) => (
                                <li key={item.id} className="flex flex-col gap-1 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p className="font-semibold text-slate-900">{item.prenom} {item.nom}</p>
                                        <p className="text-xs text-slate-500">{item.email}</p>
                                    </div>
                                    <div className="text-sm text-slate-700">
                                        {dateHeureDisponibilite(item.debut)} – {item.fin.slice(11, 16)}
                                        {item.note && <p className="text-xs text-slate-500">{item.note}</p>}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            )}

            {planningId && planning && (
                <Card
                    title={`Proposition ${planning.filiere_code} · ${semaineLisible(semaine)}`}
                    actions={<StatutPlanning statut={planning.statut} />}
                >
                    {planningQuery.isLoading ? <Spinner label="Chargement de la proposition…" /> : (
                        <div className="space-y-5">
                            {role === SCOLARITE && planning.statut === 'PREPARATION' && (
                                <PlanifierSeance
                                    planning={planning}
                                    disponibilites={disponibilites}
                                    cours={optionsCours}
                                    groupes={(groupesData?.data ?? []).filter((groupe) => String(groupe.filiere_id) === String(planning.filiere_id))}
                                    enseignants={enseignantsData?.data ?? []}
                                    salles={sallesData?.data ?? []}
                                    mutation={addSessionMutation}
                                    onError={setErreur}
                                    onSuccess={setSuccess}
                                />
                            )}

                            <ListePropositionSeances
                                planning={planning}
                                canEdit={role === SCOLARITE && planning.statut === 'PREPARATION'}
                                onRemove={(seanceId) => executerAction(deleteSessionMutation, { id: planning.id, seanceId })}
                                removing={deleteSessionMutation.isPending}
                            />

                            <div className="flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                                {role === SCOLARITE && planning.statut === 'PREPARATION' && (
                                    <Button
                                        onClick={() => executerAction(submitMutation)}
                                        loading={submitMutation.isPending}
                                        disabled={planning.seances.length === 0}
                                    >
                                        Soumettre à validation
                                    </Button>
                                )}
                                {role === SCOLARITE && planning.statut === 'A_VALIDER' && (
                                    <Button onClick={() => executerAction(validateMutation)} loading={validateMutation.isPending}>
                                        Valider l’EDT
                                    </Button>
                                )}
                                {role === ADMIN && planning.statut === 'VALIDEE' && (
                                    <Button onClick={() => executerAction(publishMutation)} loading={publishMutation.isPending}>
                                        Publier l’EDT officiel
                                    </Button>
                                )}
                                {planning.statut === 'PUBLIEE' && planning.publie_at && (
                                    <p className="text-sm text-slate-500">
                                        Publié le {new Date(planning.publie_at).toLocaleString('fr-FR')}
                                        {planning.editeur_email ? ` par ${planning.editeur_email}` : ''}.
                                    </p>
                                )}
                            </div>
                        </div>
                    )}
                </Card>
            )}
        </div>
    );
}

function PlanifierSeance({ planning, disponibilites, cours, groupes, enseignants, salles, mutation, onError, onSuccess }) {
    const [disponibiliteId, setDisponibiliteId] = useState('');
    const [form, setForm] = useState({ cours_id: '', groupe_id: '', salle_id: '', debut: '', fin: '' });
    const [fieldErrors, setFieldErrors] = useState({});
    const selection = disponibilites.find((item) => String(item.id) === disponibiliteId);
    const enseignantsDisponibles = disponibilites.reduce((ids, item) => {
        if (!ids.some((entry) => entry.id === item.enseignant_id)) {
            ids.push({ id: item.enseignant_id, nom: item.nom, prenom: item.prenom });
        }
        return ids;
    }, []);

    function choisirDisponibilite(event) {
        const id = event.target.value;
        const item = disponibilites.find((disponibilite) => String(disponibilite.id) === id);
        setDisponibiliteId(id);
        setForm((value) => ({
            ...value,
            debut: item ? dateHeureLocale(item.debut) : '',
            fin: item ? dateHeureLocale(item.fin) : '',
        }));
    }

    async function ajouter(event) {
        event.preventDefault();
        onError('');
        onSuccess('');
        setFieldErrors({});
        try {
            await mutation.mutateAsync({
                id: planning.id,
                payload: {
                    cours_id: Number(form.cours_id),
                    groupe_id: Number(form.groupe_id),
                    enseignant_id: Number(selection?.enseignant_id),
                    salle_id: Number(form.salle_id),
                    debut: dateHeureApi(form.debut),
                    fin: dateHeureApi(form.fin),
                },
            });
            setForm((value) => ({ ...value, cours_id: '', groupe_id: '', salle_id: '' }));
            setDisponibiliteId('');
            onSuccess('Séance ajoutée à la proposition.');
        } catch (error) {
            onError(extractErrorMessage(error, 'Impossible d’ajouter cette séance.'));
            setFieldErrors(extractFieldErrors(error));
        }
    }

    return (
        <form onSubmit={ajouter} className="space-y-4 rounded-2xl bg-slate-50 p-4 sm:p-5">
            <div>
                <h4 className="text-sm font-bold text-slate-900">Composer une séance</h4>
                <p className="mt-1 text-xs leading-5 text-slate-500">Le créneau doit respecter les disponibilités et les conflits de salle, de groupe et d’enseignant sont vérifiés.</p>
            </div>
            {disponibilites.length === 0 && (
                <Alert variant="warning">Aucune disponibilité enseignante n’a été déclarée pour cette semaine.</Alert>
            )}
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <Field label="Disponibilité / enseignant" required error={fieldErrors.enseignant_id?.[0]}>
                    <Select value={disponibiliteId} onChange={choisirDisponibilite} required>
                        <option value="">Choisir un créneau déclaré…</option>
                        {disponibilites.map((item) => (
                            <option key={item.id} value={item.id}>
                                {item.prenom} {item.nom} · {item.debut.slice(0, 16)}
                            </option>
                        ))}
                    </Select>
                </Field>
                <Field label="Cours" required error={fieldErrors.cours_id?.[0]}>
                    <Select value={form.cours_id} onChange={(event) => setForm({ ...form, cours_id: event.target.value })} required>
                        <option value="">Choisir un cours…</option>
                        {cours.map((item) => <option key={item.id} value={item.id}>{item.code} — {item.intitule}</option>)}
                    </Select>
                </Field>
                <Field label="Groupe" required error={fieldErrors.groupe_id?.[0]}>
                    <Select value={form.groupe_id} onChange={(event) => setForm({ ...form, groupe_id: event.target.value })} required>
                        <option value="">Choisir un groupe…</option>
                        {groupes.map((item) => <option key={item.id} value={item.id}>{item.nom}</option>)}
                    </Select>
                </Field>
                <Field label="Salle" required error={fieldErrors.salle_id?.[0]}>
                    <Select value={form.salle_id} onChange={(event) => setForm({ ...form, salle_id: event.target.value })} required>
                        <option value="">Choisir une salle…</option>
                        {salles.map((item) => <option key={item.id} value={item.id}>{item.nom} · {item.capacite} places</option>)}
                    </Select>
                </Field>
                <Field label="Début" required error={fieldErrors.debut?.[0]}>
                    <Input type="datetime-local" value={form.debut} onChange={(event) => setForm({ ...form, debut: event.target.value })} required />
                </Field>
                <Field label="Fin" required error={fieldErrors.fin?.[0]}>
                    <Input type="datetime-local" value={form.fin} onChange={(event) => setForm({ ...form, fin: event.target.value })} required />
                </Field>
            </div>
            {selection && !enseignants.some((item) => item.id === selection.enseignant_id) && (
                <p className="text-xs text-slate-500">Enseignant sélectionné : {selection.prenom} {selection.nom}</p>
            )}
            <Button type="submit" loading={mutation.isPending} disabled={!selection}>Ajouter à la proposition</Button>
        </form>
    );
}

function ListePropositionSeances({ planning, canEdit, onRemove, removing }) {
    if (!planning.seances.length) {
        return <p className="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Aucune séance ajoutée à cette proposition.</p>;
    }

    return (
        <div className="overflow-x-auto">
            <table className="min-w-[48rem] w-full text-left text-sm">
                <thead>
                    <tr className="border-b border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-400">
                        <th className="px-3 py-3">Date / horaire</th>
                        <th className="px-3 py-3">Cours</th>
                        <th className="px-3 py-3">Groupe</th>
                        <th className="px-3 py-3">Enseignant</th>
                        <th className="px-3 py-3">Salle</th>
                        {canEdit && <th className="px-3 py-3 text-right">Action</th>}
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {planning.seances.map((seance) => (
                        <tr key={seance.id}>
                            <td className="whitespace-nowrap px-3 py-3 font-medium text-slate-700">{seance.debut}</td>
                            <td className="px-3 py-3">
                                <span className="font-semibold text-slate-800">{seance.cours_code}</span>
                                <span className="block text-xs text-slate-500">{seance.cours_intitule}</span>
                            </td>
                            <td className="px-3 py-3 text-slate-600">{seance.groupe_nom}</td>
                            <td className="px-3 py-3 text-slate-600">{seance.enseignant_prenom} {seance.enseignant_nom}</td>
                            <td className="px-3 py-3 text-slate-600">{seance.salle_nom}</td>
                            {canEdit && (
                                <td className="px-3 py-3 text-right">
                                    <Button variant="ghost" loading={removing} onClick={() => onRemove(seance.id)}>Retirer</Button>
                                </td>
                            )}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

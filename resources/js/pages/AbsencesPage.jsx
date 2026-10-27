import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useMonEmploiDuTempsEnseignant } from '../features/enseignants';
import { useSeances } from '../features/seances';
import { useEtudiantsDuGroupe } from '../features/groupes';
import { useDeclarerAbsences } from '../features/absences';
import { extractErrorMessage } from '../lib/api';
import { Alert, Button, Card, Checkbox, PageHeader, Select } from '../components/ui';

export default function AbsencesPage() {
    const { isEnseignant } = useAuth();
    const [seanceId, setSeanceId] = useState('');
    const [selectedSeance, setSelectedSeance] = useState(null);

    return (
        <div>
            <PageHeader title="Absences" description="Déclarer les absences d'une séance." />

            <Card title="Choisir une séance" className="mb-6 max-w-2xl">
                {isEnseignant ? (
                    <SeanceSelectorEnseignant
                        value={seanceId}
                        onChange={(id, seance) => {
                            setSeanceId(id);
                            setSelectedSeance(seance);
                        }}
                    />
                ) : (
                    <SeanceSelectorScolarite
                        value={seanceId}
                        onChange={(id, seance) => {
                            setSeanceId(id);
                            setSelectedSeance(seance);
                        }}
                    />
                )}
            </Card>

            {seanceId && selectedSeance && <DeclarationAbsences seance={selectedSeance} />}
        </div>
    );
}

function SeanceSelectorEnseignant({ value, onChange }) {
    const { data, isLoading, error } = useMonEmploiDuTempsEnseignant();
    const jours = data?.data?.jours ?? [];
    const seances = jours.flatMap((j) => j.seances.map((s) => ({ ...s, date: j.date })));

    if (isLoading) return <Alert variant="info">Chargement de vos séances…</Alert>;
    if (error) return <Alert variant="error">{extractErrorMessage(error)}</Alert>;
    if (seances.length === 0) return <Alert variant="info">Aucune séance cette semaine.</Alert>;

    return (
        <Select
            value={value}
            onChange={(e) => {
                const s = seances.find((x) => String(x.id) === e.target.value);
                onChange(e.target.value, s);
            }}
        >
            <option value="">Sélectionner une séance…</option>
            {seances.map((s) => (
                <option key={s.id} value={s.id}>
                    {s.date} · {s.debut.slice(11, 16)}–{s.fin.slice(11, 16)} · {s.cours?.code} · {s.groupe?.nom}
                </option>
            ))}
        </Select>
    );
}

function SeanceSelectorScolarite({ value, onChange }) {
    const { data, isLoading, error } = useSeances({ per_page: 50 });
    const seances = data?.data ?? [];

    if (isLoading) return <Alert variant="info">Chargement des séances…</Alert>;
    if (error) return <Alert variant="error">{extractErrorMessage(error)}</Alert>;

    return (
        <Select
            value={value}
            onChange={(e) => {
                const s = seances.find((x) => String(x.id) === e.target.value);
                onChange(e.target.value, s);
            }}
        >
            <option value="">Sélectionner une séance…</option>
            {seances.map((s) => (
                <option key={s.id} value={s.id}>
                    {s.debut} · {s.cours?.code} · {s.groupe?.nom}
                </option>
            ))}
        </Select>
    );
}

function DeclarationAbsences({ seance }) {
    const groupeId = seance.groupe?.id;
    const { data, isLoading, error } = useEtudiantsDuGroupe(groupeId);
    const declarerMutation = useDeclarerAbsences(seance.id);
    const [absents, setAbsents] = useState({});
    const [formError, setFormError] = useState('');
    const [success, setSuccess] = useState(false);

    const etudiants = data?.data ?? [];

    function toggleAbsent(id) {
        setAbsents((prev) => {
            const next = { ...prev };
            if (next[id]) {
                delete next[id];
            } else {
                next[id] = { justifiee: false };
            }
            return next;
        });
    }

    function toggleJustifiee(id) {
        setAbsents((prev) => ({ ...prev, [id]: { justifiee: !prev[id]?.justifiee } }));
    }

    async function handleSubmit() {
        setFormError('');
        setSuccess(false);
        const absences = Object.entries(absents).map(([etudiant_id, v]) => ({
            etudiant_id: Number(etudiant_id),
            justifiee: !!v.justifiee,
        }));

        if (absences.length === 0) {
            setFormError('Sélectionnez au moins un étudiant absent.');
            return;
        }

        try {
            await declarerMutation.mutateAsync(absences);
            setSuccess(true);
            setAbsents({});
        } catch (err) {
            setFormError(extractErrorMessage(err, "Impossible d'enregistrer les absences."));
        }
    }

    return (
        <Card title={`Étudiants du groupe ${seance.groupe?.nom ?? ''}`}>
            {success && <Alert variant="success" className="mb-4">Absences enregistrées.</Alert>}
            <Alert variant="error" className="mb-4">{formError}</Alert>
            {isLoading && <Alert variant="info">Chargement…</Alert>}
            {error && <Alert variant="error">{extractErrorMessage(error)}</Alert>}

            <div className="space-y-2">
                {etudiants.map((e) => (
                    <div key={e.id} className="flex items-center justify-between rounded-md border border-slate-100 px-3 py-2">
                        <span className="text-sm text-slate-700">
                            {e.matricule} — {e.nom} {e.prenom}
                        </span>
                        <div className="flex items-center gap-4">
                            <Checkbox label="Absent" checked={!!absents[e.id]} onChange={() => toggleAbsent(e.id)} />
                            {absents[e.id] && (
                                <Checkbox label="Justifiée" checked={!!absents[e.id]?.justifiee} onChange={() => toggleJustifiee(e.id)} />
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {etudiants.length > 0 && (
                <Button className="mt-4" onClick={handleSubmit} loading={declarerMutation.isPending}>
                    Enregistrer les absences
                </Button>
            )}
        </Card>
    );
}

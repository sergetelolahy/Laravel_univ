import { useMesNotes } from '../../features/etudiantConsultation';
import { extractErrorMessage } from '../../lib/api';
import { formatDate, formatNumber } from '../../lib/format';
import { Alert, Badge, Card, PageHeader, Spinner } from '../../components/ui';

export default function MesNotesPage() {
    const { data, isLoading, error } = useMesNotes();
    const cours = data?.data ?? [];

    return (
        <div>
            <PageHeader title="Mes notes" description="Détail de mes notes par cours et par évaluation." />

            {isLoading && <Spinner label="Chargement des notes…" />}
            {error && <Alert variant="error">{extractErrorMessage(error, 'Impossible de charger les notes.')}</Alert>}

            {!isLoading && !error && cours.length === 0 && <Alert variant="info">Aucune note enregistrée pour le moment.</Alert>}

            <div className="space-y-4">
                {cours.map((c) => (
                    <Card key={c.id} title={`${c.code} — ${c.intitule}`}>
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr className="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <th className="px-3 py-2">Type</th>
                                    <th className="px-3 py-2">Pondération</th>
                                    <th className="px-3 py-2">Date</th>
                                    <th className="px-3 py-2">Note</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {c.evaluations.map((ev) => (
                                    <tr key={ev.id}>
                                        <td className="px-3 py-2">{ev.type}</td>
                                        <td className="px-3 py-2">{formatNumber(ev.ponderation)}</td>
                                        <td className="px-3 py-2">{formatDate(ev.date_eval)}</td>
                                        <td className="px-3 py-2">
                                            {ev.note === null || ev.note === undefined ? (
                                                <Badge variant="slate">Non notée</Badge>
                                            ) : (
                                                <span className="font-medium text-slate-900">{formatNumber(ev.note)} / 20</span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </Card>
                ))}
            </div>
        </div>
    );
}

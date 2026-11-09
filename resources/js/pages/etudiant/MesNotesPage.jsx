import { useMesNotes } from '../../features/etudiantConsultation';
import { extractErrorMessage } from '../../lib/api';
import { formatDate, formatNumber } from '../../lib/format';
import { Alert, Badge, Card, PageHeader, Spinner } from '../../components/ui';

function typeEvaluation(type) {
    return type === 'DEMO_CC' ? 'Contrôle continu' : type;
}

export default function MesNotesPage() {
    const { data, isLoading, error } = useMesNotes();
    const cours = data?.data ?? [];
    const evaluations = cours.flatMap((item) => item.evaluations ?? []);
    const notees = evaluations.filter((evaluation) => evaluation.note !== null && evaluation.note !== undefined);

    return (
        <div>
            <PageHeader
                title="Mes notes"
                description="Consulte les résultats publiés pour chacun de tes cours."
                actions={<Badge variant="green">{notees.length} note{notees.length > 1 ? 's' : ''}</Badge>}
            />

            {isLoading && <Spinner label="Chargement des notes…" />}
            {error && <Alert variant="error">{extractErrorMessage(error, 'Impossible de charger les notes.')}</Alert>}

            {!isLoading && !error && cours.length === 0 && (
                <Card>
                    <div className="py-8 text-center">
                        <span className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-xl text-emerald-700" aria-hidden="true">
                            ✓
                        </span>
                        <p className="mt-3 font-semibold text-slate-900">Aucun résultat publié</p>
                        <p className="mt-1 text-sm text-slate-500">Tes notes apparaîtront ici lorsqu’elles seront disponibles.</p>
                    </div>
                </Card>
            )}

            <div className="space-y-4">
                {cours.map((item) => {
                    const notesDisponibles = item.evaluations.filter((evaluation) => evaluation.note !== null && evaluation.note !== undefined);

                    return (
                        <Card
                            key={item.id}
                            title={
                                <span>
                                    <span className="text-xs font-bold uppercase tracking-wide text-emerald-700">{item.code}</span>
                                    <span className="mt-1 block text-base font-bold text-slate-900">{item.intitule}</span>
                                </span>
                            }
                            actions={<Badge variant="slate">{notesDisponibles.length}/{item.evaluations.length} publiée{item.evaluations.length > 1 ? 's' : ''}</Badge>}
                        >
                            <ul className="space-y-3 sm:hidden">
                                {item.evaluations.map((evaluation) => (
                                    <li key={evaluation.id} className="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <p className="text-sm font-semibold text-slate-800">
                                                    {typeEvaluation(evaluation.type)}
                                                    {evaluation.type === 'DEMO_CC' && (
                                                        <span className="ml-2 text-[10px] font-bold uppercase tracking-wide text-amber-700">Démo</span>
                                                    )}
                                                </p>
                                                <p className="mt-1 text-xs text-slate-500">{formatDate(evaluation.date_eval)}</p>
                                            </div>
                                            {evaluation.note === null || evaluation.note === undefined ? (
                                                <Badge variant="slate">En attente</Badge>
                                            ) : (
                                                <span className="whitespace-nowrap text-sm font-bold text-slate-900">
                                                    <span className={Number(evaluation.note) >= 10 ? 'text-emerald-700' : 'text-rose-700'}>
                                                        {formatNumber(evaluation.note)}
                                                    </span>
                                                    <span className="text-xs font-medium text-slate-400"> /20</span>
                                                </span>
                                            )}
                                        </div>
                                        <p className="mt-3 text-xs text-slate-500">
                                            Pondération : {formatNumber(evaluation.ponderation)}
                                        </p>
                                    </li>
                                ))}
                            </ul>
                            <div className="overflow-x-auto">
                                <table className="hidden min-w-[34rem] w-full text-left text-sm sm:table">
                                    <thead>
                                        <tr className="border-b border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-400">
                                            <th scope="col" className="px-3 py-3">Évaluation</th>
                                            <th scope="col" className="px-3 py-3">Pondération</th>
                                            <th scope="col" className="px-3 py-3">Date</th>
                                            <th scope="col" className="px-3 py-3 text-right">Résultat</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {item.evaluations.map((evaluation) => (
                                            <tr key={evaluation.id} className="transition hover:bg-slate-50/80">
                                                <td className="px-3 py-3.5 font-medium text-slate-800">
                                                    <span>{typeEvaluation(evaluation.type)}</span>
                                                    {evaluation.type === 'DEMO_CC' && (
                                                        <span className="ml-2 text-[10px] font-bold uppercase tracking-wide text-amber-700">Démo</span>
                                                    )}
                                                </td>
                                                <td className="px-3 py-3.5 text-slate-500">{formatNumber(evaluation.ponderation)}</td>
                                                <td className="px-3 py-3.5 text-slate-500">{formatDate(evaluation.date_eval)}</td>
                                                <td className="px-3 py-3.5 text-right">
                                                    {evaluation.note === null || evaluation.note === undefined ? (
                                                        <Badge variant="slate">En attente</Badge>
                                                    ) : (
                                                        <span className="inline-flex items-baseline gap-1 font-bold text-slate-900">
                                                            <span className={Number(evaluation.note) >= 10 ? 'text-emerald-700' : 'text-rose-700'}>
                                                                {formatNumber(evaluation.note)}
                                                            </span>
                                                            <span className="text-xs font-medium text-slate-400">/20</span>
                                                        </span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </Card>
                    );
                })}
            </div>
        </div>
    );
}

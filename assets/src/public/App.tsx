import React, { useEffect, useMemo, useState } from "react";
import { listTasks, createTask, deleteTask, updateTask, type Task } from "../shared/api";

export default function App() {
    const [title, setTitle] = useState("");
    const [desc, setDesc] = useState("");
    const [tasks, setTasks] = useState<Task[]>([]);
    const [loading, setLoading] = useState(false);

    const canDelete = ((window as any).MTM?.settings?.enable_delete ?? 1) == 1;

    const aborter = useMemo(() => new AbortController(), []);
    useEffect(() => () => aborter.abort(), [aborter]);

    const refresh = async () => {
        const res = await listTasks(undefined, aborter.signal);
        if (res?.success) setTasks(res.items || []);
    };

    useEffect(() => {
        refresh();
    }, []);

    const onSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const t = title.trim();
        const d = desc.trim();

        if (!t) return;

        setLoading(true);
        const res = await createTask({ title: t, description: d || undefined });
        setLoading(false);

        if (res?.success) {
            setTitle("");
            setDesc("");
            setTasks(res.items || []);
        } else {
            alert(res?.message || "Create failed");
        }
    };

    const onDelete = async (id: number) => {
        if (!canDelete) return; // страховка на фронте
        const res = await deleteTask(id);
        if (res?.success) setTasks(res.items || []);
        else alert(res?.message || "Delete failed");
    };

    const onRename = async (id: number, newTitle: string) => {
        const res = await updateTask(id, { title: newTitle });
        if (res?.success) {
            setTasks((prev) => prev.map((t) => (t.id === id ? res.item : t)));
        } else {
            alert(res?.message || "Update failed");
        }
    };

    return (
        <div className="mtm">
            <form className="mtm__form" onSubmit={onSubmit}>
                <label className="mtm__label">Title*</label>
                <input
                    className="mtm__input"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    maxLength={255}
                    required
                />
                <label className="mtm__label">Description</label>
                <textarea
                    className="mtm__textarea"
                    value={desc}
                    onChange={(e) => setDesc(e.target.value)}
                />
                <button className="mtm__btn" type="submit" disabled={loading}>
                    {loading ? "Saving..." : "Add task"}
                </button>
            </form>

            <h3 className="mtm__h3">Latest tasks</h3>
            <ul className="mtm__list">
                {tasks.length === 0 && <li className="mtm__empty">No tasks yet</li>}
                {tasks.map((t) => (
                    <li key={t.id} className="mtm__item">
                        <div className="mtm__item-head">
                            <strong>{t.title}</strong>
                            {canDelete && (
                                <button className="mtm__delete" onClick={() => onDelete(t.id)} aria-label="Delete">×</button>
                            )}
                        </div>
                        {t.description && <div className="mtm__desc" dangerouslySetInnerHTML={{ __html: t.description }} />}
                        {t.created_at && <div className="mtm__date">{t.created_at}</div>}
                    </li>
                ))}
            </ul>
        </div>
    );
}

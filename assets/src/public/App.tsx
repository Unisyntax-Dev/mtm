import React, { useEffect, useState } from "react";
import { listTasks, createTask, deleteTask } from "../shared/api";

type Task = { id: number; title: string; description?: string; created_at?: string };

export default function App() {
    const [title, setTitle] = useState("");
    const [desc, setDesc] = useState("");
    const [tasks, setTasks] = useState<Task[]>([]);
    const [loading, setLoading] = useState(false);

    const refresh = async () => {
        const res = await listTasks(5);
        if (res?.success) setTasks(res.items || []);
    };

    useEffect(() => { refresh(); }, []);

    const onSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!title.trim()) return;
        setLoading(true);
        const res = await createTask({ title: title.trim(), description: desc.trim() });
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
        const res = await deleteTask(id);
        if (res?.success) setTasks(res.items || []);
    };

    return (
        <div className="mtm">
            <form className="mtm__form" onSubmit={onSubmit}>
                <label className="mtm__label">Title*</label>
                <input className="mtm__input" value={title} onChange={e => setTitle(e.target.value)} maxLength={255} required />
                <label className="mtm__label">Description</label>
                <textarea className="mtm__textarea" value={desc} onChange={e => setDesc(e.target.value)} />
                <button className="mtm__btn" type="submit" disabled={loading}>{loading ? "Saving..." : "Add task"}</button>
            </form>

            <h3 className="mtm__h3">Last 5 tasks</h3>
            <ul className="mtm__list">
                {tasks.length === 0 && <li className="mtm__empty">No tasks yet</li>}
                {tasks.map(t => (
                    <li key={t.id} className="mtm__item">
                        <div className="mtm__item-head">
                            <strong>{t.title}</strong>
                            <button className="mtm__delete" onClick={() => onDelete(t.id)} aria-label="Delete">×</button>
                        </div>
                        {t.description && <div className="mtm__desc">{t.description}</div>}
                        {t.created_at && <div className="mtm__date">{t.created_at}</div>}
                    </li>
                ))}
            </ul>
        </div>
    );
}

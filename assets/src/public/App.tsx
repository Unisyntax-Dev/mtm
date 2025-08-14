import React, { useEffect, useMemo, useState } from "react";
import {
    listTasks,
    createTask,
    deleteTask,
    updateTask,
    type Task
} from "../shared/api";

/**
 * Main React component for the Mini Task Manager public UI.
 *
 * Handles:
 * - Task creation form
 * - Listing tasks
 * - Deleting tasks (if enabled in settings)
 * - Inline editing of tasks (if enabled in settings)
 */
export default function App() {
    const [title, setTitle] = useState("");
    const [desc, setDesc] = useState("");
    const [tasks, setTasks] = useState<Task[]>([]);
    const [loading, setLoading] = useState(false);

    const iconsBase = (window as any).MTM?.assets?.icons ?? "";

    const canDelete = ((window as any).MTM?.settings?.enable_delete ?? 1) == 1;
    const canEdit   = ((window as any).MTM?.settings?.enable_edit ?? 1) == 1;

    // --- Editing state ---
    const [editingId, setEditingId] = useState<number | null>(null);
    const [draftTitle, setDraftTitle] = useState("");
    const [draftDesc, setDraftDesc]   = useState("");
    const [savingId, setSavingId]     = useState<number | null>(null);

    // AbortController for request cancellation on unmount
    const aborter = useMemo(() => new AbortController(), []);
    useEffect(() => () => aborter.abort(), [aborter]);

    // Fetch tasks from API
    const refresh = async () => {
        const res = await listTasks(undefined, aborter.signal);
        if (res?.success) setTasks(res.items || []);
        else alert(res?.message || "Fetch failed");
    };

    useEffect(() => {
        refresh();
    }, []);

    // --- Create task ---
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

    // --- Delete task ---
    const onDelete = async (id: number) => {
        if (!canDelete) return;
        const res = await deleteTask(id);
        if (res?.success) setTasks(res.items || []);
        else alert(res?.message || "Delete failed");
    };

    // --- Edit handlers ---
    const startEdit = (t: Task) => {
        setEditingId(t.id);
        setDraftTitle(t.title);
        setDraftDesc(t.description || "");
    };

    const cancelEdit = () => {
        setEditingId(null);
        setDraftTitle("");
        setDraftDesc("");
        setSavingId(null);
    };

    const saveEdit = async (id: number) => {
        const newTitle = draftTitle.trim();
        const newDesc  = draftDesc.trim();

        if (!newTitle) {
            alert("Title is required");
            return;
        }

        setSavingId(id);
        const res = await updateTask(id, { title: newTitle, description: newDesc || "" });
        setSavingId(null);

        if (res?.success) {
            setTasks((prev) => prev.map((t) => (t.id === id ? res.item : t)));
            cancelEdit();
        } else {
            alert(res?.message || "Update failed");
        }
    };

    return (
        <div className="mtm">
            {/* Task creation form */}
            <form className="mtm__form" onSubmit={onSubmit}>
                <label className="mtm__label" htmlFor="mtm-title">Title*</label>
                <input
                    id="mtm-title"
                    name="title"
                    className="mtm__input"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                    maxLength={255}
                    required
                />
                <label className="mtm__label" htmlFor="mtm-desc">Description</label>
                <textarea
                    id="mtm-desc"
                    name="description"
                    className="mtm__textarea"
                    value={desc}
                    onChange={(e) => setDesc(e.target.value)}
                />
                <button className="mtm__btn mtm__btn--primary" type="submit" disabled={loading}>
                    {loading ? "Saving..." : "Add task"}
                </button>
            </form>

            <h3 className="mtm__h3">Latest tasks</h3>
            <ul className="mtm__list">
                {tasks.length === 0 && <li className="mtm__empty">No tasks yet</li>}

                {tasks.map((t) => {
                    const isEditing = editingId === t.id;
                    const isSaving  = savingId === t.id;

                    return (
                        <li key={t.id} className="mtm__item">
                            <div className="mtm__item-head">
                                {!isEditing ? (
                                    <>
                                        <div className="mtm__item-title">{t.title}</div>
                                        <div className="mtm__item-actions">
                                            {canEdit && (
                                                <button
                                                    className="mtm__iconbtn mtm__iconbtn--primary mtm__iconbtn--sm"
                                                    onClick={() => startEdit(t)}
                                                    type="button"
                                                    aria-label="Edit"
                                                    title="Edit"
                                                >
                                                    <img src={`${iconsBase}edit.svg`} alt="" className="mtm__icon" aria-hidden="true" />
                                                </button>
                                            )}
                                            {canDelete && (
                                                <button
                                                    className="mtm__iconbtn mtm__iconbtn--primary mtm__iconbtn--sm"
                                                    onClick={() => onDelete(t.id)}
                                                    type="button"
                                                    aria-label="Delete"
                                                    title="Delete"
                                                >
                                                    <img src={`${iconsBase}cross.svg`} alt="" className="mtm__icon" aria-hidden="true" />
                                                </button>
                                            )}
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <input
                                            id="mtm-edit-title"
                                            name="edit-title"
                                            className="mtm__input"
                                            value={draftTitle}
                                            onChange={(e) => setDraftTitle(e.target.value)}
                                            maxLength={255}
                                            autoFocus
                                        />
                                        <div className="mtm__item-actions">
                                            <button
                                                className="mtm__iconbtn mtm__iconbtn--primary mtm__iconbtn--sm"
                                                onClick={() => saveEdit(t.id)}
                                                disabled={isSaving}
                                                type="button"
                                                aria-label={isSaving ? "Saving..." : "Save"}
                                                title={isSaving ? "Saving..." : "Save"}
                                            >
                                                <img src={`${iconsBase}tick.svg`} alt="" className="mtm__icon" aria-hidden="true" />
                                                <span className="sr-only">{isSaving ? "Saving..." : "Save"}</span>
                                            </button>

                                            <button
                                                className="mtm__iconbtn mtm__iconbtn--primary mtm__iconbtn--sm"
                                                onClick={cancelEdit}
                                                disabled={isSaving}
                                                type="button"
                                                aria-label="Cancel"
                                                title="Cancel"
                                            >
                                                <img src={`${iconsBase}cancel.svg`} alt="" className="mtm__icon" aria-hidden="true" />
                                                <span className="sr-only">Cancel</span>
                                            </button>
                                        </div>
                                    </>
                                )}
                            </div>

                            {!isEditing ? (
                                <>
                                    {t.description && (
                                        <div
                                            className="mtm__desc"
                                            dangerouslySetInnerHTML={{ __html: t.description }}
                                        />
                                    )}
                                    {t.created_at && <div className="mtm__date">{t.created_at}</div>}
                                </>
                            ) : (
                                <div className="mtm__edit-area">
                                    <textarea
                                        id="mtm-edit-desc"
                                        name="edit-description"
                                        className="mtm__textarea"
                                        value={draftDesc}
                                        onChange={(e) => setDraftDesc(e.target.value)}
                                    />
                                </div>
                            )}
                        </li>
                    );
                })}
            </ul>
        </div>
    );
}

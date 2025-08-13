export const apiBase = (window as any).MTM?.rest ?? "/wp-json/mtm/v1";

export async function listTasks(limit = 5) {
    const r = await fetch(`${apiBase}/tasks?limit=${limit}`);
    return r.json();
}

export async function createTask(payload: { title: string; description?: string }) {
    const r = await fetch(`${apiBase}/tasks`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": (window as any).MTM?.nonce ?? "",
        },
        body: JSON.stringify(payload),
    });
    return r.json();
}

export async function deleteTask(id: number) {
    const r = await fetch(`${apiBase}/tasks/${id}`, {
        method: "DELETE",
        headers: {
            "X-WP-Nonce": (window as any).MTM?.nonce ?? "",
        },
    });
    return r.json();
}

export type Task = {
    id: number;
    title: string;
    description?: string;
    created_at?: string | null;
};

export type ApiListResp =
    | { success: true; items: Task[] }
    | { success: false; message: string };

export type ApiCreateResp =
    | { success: true; item: Task; items: Task[] }
    | { success: false; message: string };

export type ApiDeleteResp = ApiListResp;

export type ApiUpdateResp =
    | { success: true; item: Task }
    | { success: false; message: string };

export const apiBaseRaw: string = (window as any).MTM?.rest ?? "/wp-json/mtm/v1";

export const apiBase: string = apiBaseRaw.replace(/\/+$/, "");
const nonce: string = (window as any).MTM?.nonce ?? "";

type Method = "GET" | "POST" | "DELETE" | "PUT" | "PATCH";

function withQuery(url: string, q: string): string {
    return url.includes("?") ? `${url}&${q}` : `${url}?${q}`;
}

async function request<T>(
    url: string,
    method: Method = "GET",
    body?: unknown,
    signal?: AbortSignal
): Promise<T> {
    const headers: Record<string, string> = { "X-WP-Nonce": nonce };
    if (body !== undefined) headers["Content-Type"] = "application/json";

    const res = await fetch(url, {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
        signal,
        credentials: "same-origin",
    });

    let json: any = null;
    try {
        json = await res.json();
    } catch {
        //
    }

    if (!res.ok) {
        const msg = json?.message || `Request failed (${res.status})`;
        return { success: false, message: msg } as any as T;
    }
    return json as T;
}

export async function listTasks(
    limit?: number,
    signal?: AbortSignal
): Promise<ApiListResp> {
    let url = `${apiBase}/tasks`;
    if (typeof limit === "number") {
        url = withQuery(url, `limit=${Math.max(1, Math.min(100, limit))}`);
    }
    return request<ApiListResp>(url, "GET", undefined, signal);
}

export async function createTask(
    payload: { title: string; description?: string }
): Promise<ApiCreateResp> {
    return request<ApiCreateResp>(`${apiBase}/tasks`, "POST", payload);
}

export async function deleteTask(id: number): Promise<ApiDeleteResp> {
    return request<ApiDeleteResp>(`${apiBase}/tasks/${Number(id)}`, "DELETE");
}

export async function updateTask(
    id: number,
    payload: Partial<{ title: string; description: string }>
): Promise<ApiUpdateResp> {
    return request<ApiUpdateResp>(
        `${apiBase}/tasks/${Number(id)}`,
        "PATCH",
        payload
    );
}

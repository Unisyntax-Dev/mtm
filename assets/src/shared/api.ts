/**
 * Type definitions for Task objects and API responses.
 */

// Task model
export type Task = {
    id: number;               // Unique task ID
    title: string;            // Task title
    description?: string;     // Task description (optional)
    created_at?: string | null; // Creation date/time in localized format (optional, nullable)
};

// API response types
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

/**
 * API configuration — base URL and nonce injected from wp_localize_script.
 */
export const apiBaseRaw: string = (window as any).MTM?.rest ?? "/wp-json/mtm/v1";
export const apiBase: string = apiBaseRaw.replace(/\/+$/, "");
const nonce: string = (window as any).MTM?.nonce ?? "";

// HTTP method type
type Method = "GET" | "POST" | "DELETE" | "PUT" | "PATCH";

/**
 * Helper to append query params to a URL.
 */
function withQuery(url: string, q: string): string {
    return url.includes("?") ? `${url}&${q}` : `${url}?${q}`;
}

/**
 * Generic fetch wrapper for API requests.
 *
 * @param url    Endpoint URL
 * @param method HTTP method
 * @param body   Optional request body (JSON)
 * @param signal Optional AbortSignal
 */
async function request<T>(
    url: string,
    method: Method = "GET",
    body?: unknown,
    signal?: AbortSignal
): Promise<T> {
    url = withQuery(url, `_wpnonce=${encodeURIComponent(nonce)}`);

    const headers: Record<string, string> = { "X-WP-Nonce": nonce };
    if (body !== undefined) headers["Content-Type"] = "application/json";

    let res: Response;
    try {
        res = await fetch(url, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
            signal,
            credentials: "same-origin",
        });
    } catch (err: any) {
        return {
            success: false,
            message: err?.message || "Network error",
        } as any as T;
    }

    let json: any = null;
    try {
        json = await res.json();
    } catch {
        // ignore JSON parse errors — will be handled below
    }

    if (!res.ok) {
        const msg =
            json?.message ||
            (res.status === 403
                ? "Forbidden"
                : `Request failed (${res.status})`);
        return { success: false, message: msg } as any as T;
    }
    return json as T;
}

/**
 * Fetch the list of recent tasks.
 *
 * @param limit  Optional limit (1–100)
 * @param signal Optional AbortSignal for cancellation
 */
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

/**
 * Create a new task.
 *
 * @param payload Object with title and optional description
 */
export async function createTask(
    payload: { title: string; description?: string }
): Promise<ApiCreateResp> {
    return request<ApiCreateResp>(`${apiBase}/tasks`, "POST", payload);
}

/**
 * Delete a task by ID.
 *
 * @param id Task ID
 */
export async function deleteTask(id: number): Promise<ApiDeleteResp> {
    return request<ApiDeleteResp>(`${apiBase}/tasks/${Number(id)}`, "DELETE");
}

/**
 * Update a task by ID.
 *
 * @param id      Task ID
 * @param payload Partial object with title and/or description
 */
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

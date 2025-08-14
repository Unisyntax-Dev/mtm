// Defines the shape of a Task object used in the Mini Task Manager.
// Optional fields are marked with '?'.
export type Task = {
    id: number;          // Unique task ID (from database)
    title: string;       // Task title (required)
    description?: string; // Task description (optional)
    created_at?: string;  // Creation date/time in localized format (optional)
};

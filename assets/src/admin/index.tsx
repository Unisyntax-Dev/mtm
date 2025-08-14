import React from "react";
import { createRoot } from "react-dom/client";

/**
 * Entry point for the Mini Task Manager admin React app.
 *
 * Currently renders a placeholder; extend this component to implement
 * admin-specific UI such as task management tools or reports.
 */
const AdminApp = () => <div>Mini Task Manager (admin)</div>;

// Mount the admin app into the element with ID "mtm-admin-root"
const el = document.getElementById("mtm-admin-root");
if (el) {
    createRoot(el).render(<AdminApp />);
} else {
    console.error("mtm-admin-root not found");
}

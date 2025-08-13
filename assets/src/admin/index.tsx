import React from "react";
import { createRoot } from "react-dom/client";

const AdminApp = () => <div>Mini Task Manager (admin)</div>;

const el = document.getElementById("mtm-admin-root");
if (el) {
  createRoot(el).render(<AdminApp />);
} else {
  console.error("mtm-admin-root not found");
}

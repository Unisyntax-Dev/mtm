import React from "react";
import { createRoot } from "react-dom/client";

const App = () => <div>Mini Task Manager (public)</div>;

const el = document.getElementById("mtm-root");
if (el) {
  createRoot(el).render(<App />);
} else {
  console.error("mtm-root not found");
}

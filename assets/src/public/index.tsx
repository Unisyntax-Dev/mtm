import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";
import "./scss/public.scss";

/**
 * Entry point for the public-facing React app.
 *
 * Mounts the <App /> component into the DOM element with ID "mtm-root".
 * Styles are imported from SCSS, compiled via Webpack.
 */

const el = document.getElementById("mtm-root");
if (el) {
    createRoot(el).render(<App />);
}

import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";
import "./scss/public.scss";

const el = document.getElementById("mtm-root");
if (el) createRoot(el).render(<App />);

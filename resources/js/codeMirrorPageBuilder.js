import { EditorView, basicSetup } from "codemirror";
import { html } from "@codemirror/lang-html";
import { oneDark } from "@codemirror/theme-one-dark";
import axios from "axios";
import * as prettier from "prettier/standalone";
import htmlPlugin from "prettier/plugins/html";

let editorView;

async function formatHTML(code) {
    return prettier.format(code, {
        parser: "html",
        plugins: [htmlPlugin],
        printWidth: 120,
        tabWidth: 4,
        useTabs: false,
    });
}

const createEditor = (elementId, initialValue = "") => {
    const targetElement = document.getElementById(elementId);

    if (!targetElement) return;

    editorView = new EditorView({
        doc: initialValue,
        extensions: [
            basicSetup,
            html(),
            oneDark,
            EditorView.updateListener.of((update) => {
                if (update.docChanged) {
                    const code = update.state.doc.toString();
                    const preview = document.getElementById('preview-iframe');
                    if (preview) {
                        preview.srcdoc = code;
                    }
                }
            }),
        ],
        parent: targetElement,
    });

    return editorView;
};

function saveContent() {
    if (!editorView || !window.post) return;

    const btn = document.getElementById('save-btn');
    const status = document.getElementById('save-status');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!csrfToken) {
        status.textContent = 'Error: Token CSRF no encontrado';
        return;
    }

    btn.disabled = true;
    status.textContent = 'Guardando...';

    const content = editorView.state.doc.toString();

    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    axios.post(`/dashboard/pages/${window.post.slug}/update-code`, { content })
        .then(() => {
            status.textContent = 'Guardado correctamente';
            setTimeout(() => { status.textContent = ''; }, 2000);
        })
        .catch((err) => {
            console.error('Error al guardar:', err);
            status.textContent = 'Error al guardar';
        })
        .finally(() => {
            btn.disabled = false;
        });
}

async function formatEditorContent() {
    if (!editorView) return;
    const code = editorView.state.doc.toString();
    try {
        const formatted = await formatHTML(code);
        editorView.dispatch({
            changes: { from: 0, to: editorView.state.doc.length, insert: formatted },
        });
    } catch (err) {
        console.error('Error al formatear:', err);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", async () => {
    const rawContent = window.post?.content || "<h1>Hola Laravel</h1>";

    // Formatear el contenido inicial antes de cargarlo
    let initialContent = rawContent;
    try {
        initialContent = await formatHTML(rawContent);
    } catch (e) {
        console.warn('No se pudo formatear el contenido inicial:', e);
    }

    createEditor("codemirror-editor", initialContent);

    // Renderizar el preview inicial (con el contenido original sin formatear)
    const preview = document.getElementById('preview-iframe');
    if (preview) {
        preview.srcdoc = rawContent;
    }

    document.getElementById('save-btn')?.addEventListener('click', saveContent);
    document.getElementById('format-btn')?.addEventListener('click', formatEditorContent);

    // Atajo Ctrl+S / Cmd+S
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveContent();
        }
    });

    // --- Resizable divider ---
    initDivider();

    // --- Layout toggle ---
    initLayoutToggle();
});

function initDivider() {
    const divider = document.getElementById('divider');
    const container = document.getElementById('editor-container');
    const editorPanel = document.getElementById('codemirror-editor');
    const previewPanel = document.getElementById('preview-container');
    if (!divider || !container || !editorPanel || !previewPanel) return;

    let dragging = false;

    divider.addEventListener('mousedown', (e) => {
        e.preventDefault();
        dragging = true;
        divider.classList.add('dragging');
        document.body.style.cursor = container.classList.contains('vertical') ? 'row-resize' : 'col-resize';
        // Desactivar pointer events en el iframe mientras se arrastra
        previewPanel.style.pointerEvents = 'none';
    });

    document.addEventListener('mousemove', (e) => {
        if (!dragging) return;
        const rect = container.getBoundingClientRect();
        const isVertical = container.classList.contains('vertical');

        if (isVertical) {
            const offsetY = e.clientY - rect.top;
            const pct = (offsetY / rect.height) * 100;
            const clamped = Math.min(Math.max(pct, 10), 90);
            editorPanel.style.flex = `0 0 ${clamped}%`;
            previewPanel.style.flex = `0 0 ${100 - clamped}%`;
        } else {
            const offsetX = e.clientX - rect.left;
            const pct = (offsetX / rect.width) * 100;
            const clamped = Math.min(Math.max(pct, 10), 90);
            editorPanel.style.flex = `0 0 ${clamped}%`;
            previewPanel.style.flex = `0 0 ${100 - clamped}%`;
        }
    });

    document.addEventListener('mouseup', () => {
        if (!dragging) return;
        dragging = false;
        divider.classList.remove('dragging');
        document.body.style.cursor = '';
        previewPanel.style.pointerEvents = '';
    });
}

function initLayoutToggle() {
    const container = document.getElementById('editor-container');
    const btnH = document.getElementById('layout-horizontal');
    const btnV = document.getElementById('layout-vertical');
    const editorPanel = document.getElementById('codemirror-editor');
    const previewPanel = document.getElementById('preview-container');
    if (!container || !btnH || !btnV) return;

    function resetFlex() {
        editorPanel.style.flex = '';
        previewPanel.style.flex = '';
    }

    btnH.addEventListener('click', () => {
        container.classList.remove('vertical');
        btnH.classList.add('active');
        btnV.classList.remove('active');
        resetFlex();
    });

    btnV.addEventListener('click', () => {
        container.classList.add('vertical');
        btnV.classList.add('active');
        btnH.classList.remove('active');
        resetFlex();
    });
}
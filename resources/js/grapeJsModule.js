import grapesjs from "grapesjs";
import blocksBasic from "grapesjs-blocks-basic";
import presetWebpage from "grapesjs-preset-webpage";
import componentCodeEditor from "grapesjs-component-code-editor";
import "grapesjs/dist/css/grapes.min.css";
import "grapesjs-component-code-editor/dist/grapesjs-component-code-editor.min.css";

// Variable global para el editor
let grapesEditor;
const API = {
    PAGE_EDIT_CONTENT: (id) => `/dashboard/pages/${id}/update-content`,
    PAGES: "/pages",
};
document.addEventListener("DOMContentLoaded", () => {
    const editorContainer = document.getElementById("gjs");
    if (!editorContainer) {
        console.error("No se encontró el contenedor para GrapesJS");
        return;
    }
    grapesEditor = grapesjs.init({
            container: "#gjs",
            height: "100vh",
            width: "auto",
            storageManager: false,
            fromElement: true,
            plugins: [blocksBasic, presetWebpage, componentCodeEditor],
            pluginsOpts: {
                [componentCodeEditor]: {
                    panelId: 'views-container',
                    appendTo: '.gjs-pn-views-container',
                    openState: {
                        cv: '65%', // Canvas width
                        pn: '35%'  // Panel width
                    },
                    closedState: {
                        cv: '85%',
                        pn: '15%'
                    },
                    codeViewOptions: {
                        theme: 'hopscotch',
                        autoBeautify: true,
                        autoCloseTags: true,
                        autoCloseBrackets: true,
                        lineWrapping: true,
                        styleActiveLine: true,
                        smartIndent: true,
                        indentWithTabs: true
                    },
                    editJs: false, // Deshabilitar JS si no lo necesitas
                    editCss: false, // Deshabilitar CSS si no lo necesitas
                    clearData: false
                }
            },
            // Habilitar edición de código
            panels: {
                defaults: [
                    {
                        id: "layers",
                        el: ".panel__right",
                        resizable: {
                            maxDim: 350,
                            minDim: 200,
                            tc: 0, // Top handler
                            cl: 1, // Left handler
                        },
                    },
                    {
                        id: "panel-switcher",
                        el: ".panel__switcher",
                        buttons: [
                            {
                                id: "show-layers",
                                active: true,
                                label: "Layers",
                                command: "show-layers",
                            },
                            {
                                id: "show-style",
                                active: true,
                                label: "Styles",
                                command: "show-styles",
                            },
                            {
                                id: "show-code",
                                label: "Code",
                                command: "export-template",
                                context: "export-template",
                            },
                        ],
                    },
                ],
            },
            // Configuración del editor de código
            codeManager: {
                enabled: true, // Habilitar el administrador de código integrado
                inlineCss: true,
                // Habilitar edición
                codeMirror: {
                    theme: "hopscotch",
                    readOnly: false, // Esto habilita la edición
                    autoBeautify: true,
                    autoCloseTags: true,
                    autoCloseBrackets: true,
                    lineWrapping: true,
                    styleActiveLine: true,
                    smartIndent: true,
                },
            },
            canvas: {
                //styles: ["data:text/css;charset=utf-8," + encodeURIComponent(window.canvas_style)],
            },
        });

        // Exponer el editor para depuración
        window.grapesEditor = grapesEditor;

        // Cargar CDNs cuando el iframe del canvas esté listo
        grapesEditor.on('canvas:frame:load', ({ window: frameWindow }) => {
            loadSavedCdns(grapesEditor, frameWindow.document);
        });

        // Agregar bloques personalizados DESPUÉS de verificar los básicos
        //addBlocksPersonalizados();
        // Botón para guardar el contenido
        addButtonSave();
        //addButtonReturnPage();
        // Botones para editar código
        addCodeEditorButtons();
        // Botón para gestionar CDNs
        addCdnManagerButton();
    

});

// Función para agregar un botón de guardar
function addButtonSave() {
    grapesEditor.Panels.addButton("options", {
        id: "save-db",
        className: "fa fa-save",
        command: "save-to-db",
        attributes: { title: "Guardar Cambios" },
    });
    // Definir el comando para guardar en Laravel
    grapesEditor.Commands.add("save-to-db", {
        run: (editor) => {
            
            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            )?.content;
            const apiUrl = API.PAGE_EDIT_CONTENT(post.slug);
            
            if (!csrfToken) {
                alert("Error: Token CSRF no encontrado. Recarga la página.");
                return;
            }
            let html = editor.getHtml();
            const css = editor.getCss();
            let cdns = getSavedCdns();
            console.log("HTML:", html);
            console.log("CSS:", css);
            console.log("CDNs:", cdns);
            // si es un componente quitar el <body> y </body>
            if (post.type_id == 4) {
                html = html.replace(/<body[^>]*>/, "").replace(/<\/body>/, "");
            }

            fetch(apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    content_body: html,
                    content_css: css,
                    cdns: cdns,
                }),
            })
                .then((response) => {
                    if (response.status === 302) {
                        console.error(
                            "Error de redirección (302). Verifica autenticación y CSRF token."
                        );
                        alert(
                            "Error de autenticación. Recarga la página e intenta de nuevo."
                        );
                        return;
                    }
                    if (!response.ok) {
                        throw new Error(
                            `HTTP error! status: ${response.status}`
                        );
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data) {
                        alert("Guardado correctamente");
                        console.log("Respuesta del servidor:", data);
                    }
                })
                .catch((err) => {
                    console.error("Error completo:", err);
                    alert(
                        "Error al guardar. Revisa la consola para más detalles."
                    );
                });
        },
    });
}
// funcion para volver atras
function addButtonReturnPage() {
    grapesEditor.Panels.addButton("options", {
        id: "return-page",
        className: "fa fa-arrow-left",
        command: "return-to-page",
        attributes: { title: "Volver a la lista de páginas" },
    });
    grapesEditor.Commands.add("return-to-page", {
        run: (editor) => {
            // Redirigir atras
            window.location.href = API.PAGES;
        },
    });
}

function addCodeEditorButtons() {
    const pn = grapesEditor.Panels;
    const panelViews = pn.addPanel({
        id: "views",
    });
    panelViews.get("buttons").add([
        {
            attributes: {
                title: "Open Code",
            },
            className: "fa fa-file-code-o",
            command: "open-code",
            togglable: false, //do not close when button is clicked again
            id: "open-code",
        },
    ]);
}

function addBlocksPersonalizados() {
    setTimeout(() => {
        grapesEditor.BlockManager.add("my-block-id", {
            label: "Texto Simple",
            content: '<div class="my-block">Texto editable</div>',
            category: "Personalizado",
            attributes: { class: "gjs-block-section" },
        });

        grapesEditor.BlockManager.add("heading-block", {
            label: "Título Personalizado",
            content: "<h1>Mi Título</h1>",
            category: "Personalizado",
            attributes: { class: "gjs-block-section" },
        });

        grapesEditor.BlockManager.add("2-column-block", {
            label: "2 Columnas Bootstrap",
            content: `
                    <div class="row">
                        <div class="col-md-6">
                            <p>Columna 1</p>
                        </div>
                        <div class="col-md-6">
                            <p>Columna 2</p>
                        </div>
                    </div>
                `,
            category: "Personalizado",
            attributes: { class: "gjs-block-section" },
        });

        // Verificar todos los bloques después de agregar personalizados
        console.log(
            "Total de bloques después de agregar personalizados:",
            grapesEditor.BlockManager.getAll().length
        );

        // Agrupar por categorías
        const categories = {};
        grapesEditor.BlockManager.getAll().forEach((block) => {
            const category = block.get("category") || "Sin categoría";
            if (!categories[category]) categories[category] = [];
            categories[category].push(block.get("label"));
        });
        console.log("Bloques por categoría:", categories);
    }, 200);
}

// ============================================================
// CDN Manager - Permite agregar/eliminar scripts y estilos CDN
// ============================================================

const CDN_STORAGE_KEY = () => `gjs-cdns-${window.post?.id || 'default'}`;

function getSavedCdns() {
    try {
        const data = localStorage.getItem(CDN_STORAGE_KEY());
        return data ? JSON.parse(data) : { scripts: [], styles: [] };
    } catch {
        return { scripts: [], styles: [] };
    }
}

function saveCdns(cdns) {
    localStorage.setItem(CDN_STORAGE_KEY(), JSON.stringify(cdns));
}

function loadSavedCdns(editor, iframeDoc) {
    // Unificar CDNs del post (BD) y localStorage sin duplicados
    const postCdns = window.post?.cdns || { scripts: [], styles: [] };
    const localCdns = getSavedCdns();

    const merged = {
        scripts: [...new Set([
            ...(Array.isArray(postCdns.scripts) ? postCdns.scripts : []),
            ...localCdns.scripts,
        ])],
        styles: [...new Set([
            ...(Array.isArray(postCdns.styles) ? postCdns.styles : []),
            ...localCdns.styles,
        ])],
    };

    // Sincronizar localStorage con la lista unificada
    saveCdns(merged);

    // Inyectar directamente en el documento del iframe ya cargado
    merged.scripts.forEach((url) => {
        injectToDoc(iframeDoc, 'script', url);
    });

    merged.styles.forEach((url) => {
        injectToDoc(iframeDoc, 'style', url);
    });
}

// Inyecta directamente en un document (sin depender de canvas.getFrameEl)
function injectToDoc(doc, type, url) {
    if (!doc) return;
    if (type === 'script') {
        if (doc.querySelector(`script[src="${url}"]`)) return;
        const el = doc.createElement('script');
        el.src = url;
        doc.head.appendChild(el);
    } else {
        if (doc.querySelector(`link[href="${url}"]`)) return;
        const el = doc.createElement('link');
        el.rel = 'stylesheet';
        el.href = url;
        doc.head.appendChild(el);
    }
}

function injectScriptToCanvas(canvas, url) {
    const frame = canvas.getFrameEl();
    if (!frame) return;
    const doc = frame.contentDocument;
    if (!doc) return;
    // Evitar duplicados
    if (doc.querySelector(`script[src="${url}"]`)) return;
    const script = doc.createElement('script');
    script.src = url;
    doc.head.appendChild(script);
}

function injectStyleToCanvas(canvas, url) {
    const frame = canvas.getFrameEl();
    if (!frame) return;
    const doc = frame.contentDocument;
    if (!doc) return;
    // Evitar duplicados
    if (doc.querySelector(`link[href="${url}"]`)) return;
    const link = doc.createElement('link');
    link.rel = 'stylesheet';
    link.href = url;
    doc.head.appendChild(link);
}

function removeScriptFromCanvas(canvas, url) {
    const frame = canvas.getFrameEl();
    if (!frame) return;
    const doc = frame.contentDocument;
    if (!doc) return;
    const el = doc.querySelector(`script[src="${url}"]`);
    if (el) el.remove();

    // Algunos scripts (como Tailwind CSS browser) generan <style> dinámicamente.
    // Eliminamos todos los <style> que no sean del editor para limpiar estilos residuales.
    const generatedStyles = doc.querySelectorAll('style:not([id^="gjs"])');
    generatedStyles.forEach((style) => style.remove());

    // Forzar un re-render del canvas para que se apliquen los cambios
    const wrapper = canvas.getBody();
    if (wrapper) {
        wrapper.style.display = 'none';
        // eslint-disable-next-line no-unused-expressions
        wrapper.offsetHeight; // force reflow
        wrapper.style.display = '';
    }
}

function removeStyleFromCanvas(canvas, url) {
    const frame = canvas.getFrameEl();
    if (!frame) return;
    const doc = frame.contentDocument;
    if (!doc) return;
    const el = doc.querySelector(`link[href="${url}"]`);
    if (el) el.remove();
}

function buildCdnModalContent(editor) {
    const cdns = getSavedCdns();

    const container = document.createElement('div');
    container.style.cssText = 'padding: 15px; font-family: sans-serif; color: #ddd; max-height: 70vh; overflow-y: auto;';

    container.innerHTML = `
        <style>
            .cdn-section { margin-bottom: 20px; }
            .cdn-section h3 { margin: 0 0 10px; font-size: 14px; color: #fff; border-bottom: 1px solid #555; padding-bottom: 5px; }
            .cdn-input-row { display: flex; gap: 8px; margin-bottom: 10px; }
            .cdn-input-row input { flex: 1; padding: 8px 10px; border: 1px solid #555; border-radius: 4px; background: #2b2b2b; color: #fff; font-size: 13px; }
            .cdn-input-row input::placeholder { color: #888; }
            .cdn-btn { padding: 8px 14px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; }
            .cdn-btn-add { background: #4CAF50; color: #fff; }
            .cdn-btn-add:hover { background: #45a049; }
            .cdn-btn-remove { background: #e74c3c; color: #fff; padding: 4px 10px; font-size: 12px; }
            .cdn-btn-remove:hover { background: #c0392b; }
            .cdn-list { list-style: none; padding: 0; margin: 0; }
            .cdn-list li { display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; background: #2b2b2b; border-radius: 4px; margin-bottom: 4px; font-size: 12px; word-break: break-all; }
            .cdn-list li span { flex: 1; margin-right: 10px; color: #aef; }
            .cdn-empty { color: #888; font-size: 12px; font-style: italic; }
        </style>

        <div class="cdn-section">
            <h3>📜 Scripts (JS CDN)</h3>
            <div class="cdn-input-row">
                <input type="text" id="cdn-script-input" placeholder="https://cdn.jsdelivr.net/npm/library@version/dist/lib.min.js" />
                <button class="cdn-btn cdn-btn-add" id="cdn-add-script">Agregar</button>
            </div>
            <ul class="cdn-list" id="cdn-script-list"></ul>
        </div>

        <div class="cdn-section">
            <h3>🎨 Estilos (CSS CDN)</h3>
            <div class="cdn-input-row">
                <input type="text" id="cdn-style-input" placeholder="https://cdn.jsdelivr.net/npm/library@version/dist/lib.min.css" />
                <button class="cdn-btn cdn-btn-add" id="cdn-add-style">Agregar</button>
            </div>
            <ul class="cdn-list" id="cdn-style-list"></ul>
        </div>
    `;

    // Render las listas actuales
    function renderList(listEl, items, type) {
        listEl.innerHTML = '';
        if (items.length === 0) {
            listEl.innerHTML = '<li class="cdn-empty">No hay CDNs agregados</li>';
            return;
        }
        items.forEach((url, index) => {
            const li = document.createElement('li');
            li.innerHTML = `<span>${url}</span>`;
            const btn = document.createElement('button');
            btn.className = 'cdn-btn cdn-btn-remove';
            btn.textContent = '✕';
            btn.title = 'Eliminar';
            btn.addEventListener('click', () => {
                const currentCdns = getSavedCdns();
                currentCdns[type].splice(index, 1);
                saveCdns(currentCdns);
                if (type === 'scripts') {
                    removeScriptFromCanvas(editor.Canvas, url);
                } else {
                    removeStyleFromCanvas(editor.Canvas, url);
                }
                renderList(listEl, currentCdns[type], type);
            });
            li.appendChild(btn);
            listEl.appendChild(li);
        });
    }

    // Renderizar después de insertar en el DOM
    setTimeout(() => {
        const scriptList = container.querySelector('#cdn-script-list');
        const styleList = container.querySelector('#cdn-style-list');

        renderList(scriptList, cdns.scripts, 'scripts');
        renderList(styleList, cdns.styles, 'styles');

        // Agregar script
        container.querySelector('#cdn-add-script').addEventListener('click', () => {
            const input = container.querySelector('#cdn-script-input');
            const url = input.value.trim();
            if (!url) return;
            if (!url.startsWith('http')) {
                alert('La URL debe comenzar con http:// o https://');
                return;
            }
            const currentCdns = getSavedCdns();
            if (currentCdns.scripts.includes(url)) {
                alert('Este script ya está agregado');
                return;
            }
            currentCdns.scripts.push(url);
            saveCdns(currentCdns);
            injectScriptToCanvas(editor.Canvas, url);
            input.value = '';
            renderList(scriptList, currentCdns.scripts, 'scripts');
        });

        // Agregar estilo
        container.querySelector('#cdn-add-style').addEventListener('click', () => {
            const input = container.querySelector('#cdn-style-input');
            const url = input.value.trim();
            if (!url) return;
            if (!url.startsWith('http')) {
                alert('La URL debe comenzar con http:// o https://');
                return;
            }
            const currentCdns = getSavedCdns();
            if (currentCdns.styles.includes(url)) {
                alert('Este estilo ya está agregado');
                return;
            }
            currentCdns.styles.push(url);
            saveCdns(currentCdns);
            injectStyleToCanvas(editor.Canvas, url);
            input.value = '';
            renderList(styleList, currentCdns.styles, 'styles');
        });

        // Permitir agregar con Enter
        container.querySelector('#cdn-script-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') container.querySelector('#cdn-add-script').click();
        });
        container.querySelector('#cdn-style-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') container.querySelector('#cdn-add-style').click();
        });
    }, 50);

    return container;
}

function addCdnManagerButton() {
    grapesEditor.Panels.addButton("options", {
        id: "cdn-manager",
        className: "fa fa-link",
        command: "open-cdn-manager",
        attributes: { title: "Gestionar CDNs (Scripts y Estilos)" },
    });

    grapesEditor.Commands.add("open-cdn-manager", {
        run: (editor) => {
            const modal = editor.Modal;
            modal.setTitle('Gestionar CDNs');
            modal.setContent(buildCdnModalContent(editor));
            modal.open();
        },
    });
}

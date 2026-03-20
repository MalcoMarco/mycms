import grapesjs from "grapesjs";
import blocksBasic from "grapesjs-blocks-basic";
import presetWebpage from "grapesjs-preset-webpage";
import componentCodeEditor from "grapesjs-component-code-editor";
import "grapesjs/dist/css/grapes.min.css";
import "grapesjs-component-code-editor/dist/grapesjs-component-code-editor.min.css";
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Variable global para el editor
let grapejsEditor;


const API = {
    PAGE_EDIT_CONTENT: (id) => `/dashboard/pages/${id}/update-content`,
    PAGES: "/pages",
};

const CUSTOM_CANVAS_SCRIPT_ID = "gjs-custom-page-script";

document.addEventListener("DOMContentLoaded", () => {
    const editorContainer = document.getElementById("gjs");
    if (!editorContainer) {
        console.error("No se encontró el contenedor para grapesJS");
        return;
    }
    grapejsEditor = grapesjs.init({
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
                editJs: false,
                editCss: true,
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
            scripts: window.tailwindCdn ? [window.tailwindCdn] : [],
            styles: window.webSettings?.canvas_styles
                ? ["data:text/css;charset=utf-8," + encodeURIComponent(window.webSettings.canvas_styles)]
                : [],
        },

    });

    // Exponer el editor para depuración
    window.grapejsEditor = grapejsEditor;

    // Agregar bloques personalizados DESPUÉS de verificar los básicos
    // Botón para guardar el contenido
    addButtonSave();
    //addButtonReturnPage();
    // Botones para editar código
    addCodeEditorButtons();
    addCustomCodeEditorButton();

    // Aplicar atributos originales del <body> al canvas
    grapejsEditor.on("load", () => {
        const wrapper = grapejsEditor.getWrapper();
        let bodyClass = document.getElementById('gjs')?.dataset.bodyClass || '';
        wrapper.setAttributes({
            class: bodyClass,
        });
    });
});

// Función para agregar un botón de guardar
function addButtonSave() {
    grapejsEditor.Panels.addButton("options", {
        id: "save-db",
        className: "fa fa-save",
        command: "save-to-db",
        attributes: { title: "Guardar Cambios" },
    });
    // Definir el comando para guardar en Laravel
    grapejsEditor.Commands.add("save-to-db", {
        run: (editor) => {

            const csrfToken = document.querySelector(
                'meta[name="csrf-token"]'
            )?.content;
            const apiUrl = API.PAGE_EDIT_CONTENT(post.slug);

            if (!csrfToken) {
                alert("Error: Token CSRF no encontrado. Recarga la página.");
                return;
            }
            let htmlBody = editor.getHtml();
            let css = editor.getCss();
            let js = editor.getJs();

            // Re-agregar los <script> originales del body que GrapesJS eliminó
            if (window.bodyScripts) {
                if (htmlBody.includes('</body>')) {
                    htmlBody = htmlBody.replace('</body>', '\n' + window.bodyScripts + '\n</body>');
                } else {
                    js += '\n' + window.bodyScripts;
                }
            }

            console.log(htmlBody);
            console.log('js',js);
            
            // si es un componente quitar el <body> y </body>
            // if (post.type_id == 4) {
            //     html = html.replace(/<body[^>]*>/, "").replace(/<\/body>/, "");
            // }
             //return; // Eliminar esta línea para habilitar el guardado real
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
            axios.post(apiUrl, {
                content: htmlBody,
                css: css,
                js: js,
            })
                .then((response) => {
                    alert("Guardado correctamente");
                    console.log("Respuesta del servidor:", response.data);
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
    grapejsEditor.Panels.addButton("options", {
        id: "return-page",
        className: "fa fa-arrow-left",
        command: "return-to-page",
        attributes: { title: "Volver a la lista de páginas" },
    });
    grapejsEditor.Commands.add("return-to-page", {
        run: (editor) => {
            // Redirigir atras
            window.location.href = API.PAGES;
        },
    });
}

function addCodeEditorButtons() {
    const pn = grapejsEditor.Panels;
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
        {
            attributes: {
                title: "Editar CSS y JS",
            },
            className: "fa fa-code",
            command: "open-custom-code-editor",
            togglable: false,
            id: "open-custom-code-editor",
        },
    ]);
}

function addCustomCodeEditorButton() {
    grapejsEditor.Commands.add("open-custom-code-editor", {
        run: (editor) => {
            const modal = editor.Modal;
            modal.setTitle("Editar CSS y JS");
            modal.setContent(buildCustomCodeModalContent(editor));
            modal.open();
        },
    });
}

function buildCustomCodeModalContent(editor) {
    const container = document.createElement("div");
    container.style.cssText = "padding: 16px; color: #ddd; font-family: sans-serif; max-height: 75vh; overflow-y: auto;";

    container.innerHTML = `
        <style>
            .custom-code-section { margin-bottom: 18px; }
            .custom-code-title { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; color: #fff; }
            .custom-code-help { margin: 0 0 8px; font-size: 12px; color: #9ca3af; }
            .custom-code-editor { width: 100%; min-height: 220px; padding: 12px; border: 1px solid #555; border-radius: 6px; background: #1f2937; color: #f9fafb; font: 13px/1.5 monospace; resize: vertical; box-sizing: border-box; }
            .custom-code-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 12px; }
            .custom-code-button { padding: 10px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
            .custom-code-button-secondary { background: #374151; color: #fff; }
            .custom-code-button-primary { background: #2563eb; color: #fff; }
        </style>

        <div class="custom-code-section">
            <label class="custom-code-title" for="custom-css-editor">CSS global</label>
            <p class="custom-code-help">Este CSS se aplica al canvas y se guarda junto con la página.</p>
            <textarea id="custom-css-editor" class="custom-code-editor" spellcheck="false"></textarea>
        </div>

        <div class="custom-code-section">
            <label class="custom-code-title" for="custom-js-editor">JavaScript global</label>
            <p class="custom-code-help">Escribe solo JavaScript. Si pegas etiquetas &lt;script&gt;, se limpiarán al guardar.</p>
            <textarea id="custom-js-editor" class="custom-code-editor" spellcheck="false"></textarea>
        </div>

        <div class="custom-code-actions">
            <button type="button" class="custom-code-button custom-code-button-secondary" id="custom-code-cancel">Cerrar</button>
            <button type="button" class="custom-code-button custom-code-button-primary" id="custom-code-apply">Aplicar</button>
        </div>
    `;

    setTimeout(() => {
        const cssEditor = container.querySelector("#custom-css-editor");
        const jsEditor = container.querySelector("#custom-js-editor");
        const applyButton = container.querySelector("#custom-code-apply");
        const cancelButton = container.querySelector("#custom-code-cancel");

        cssEditor.value = editor.getCss();
        jsEditor.value = customJsCode;

        applyButton.addEventListener("click", () => {
            editor.setStyle(cssEditor.value);
            customJsCode = normalizeJsContent(jsEditor.value);
            applyCustomCodeToCanvas(getCanvasDocument());
            editor.Modal.close();
        });

        cancelButton.addEventListener("click", () => {
            editor.Modal.close();
        });
    }, 50);

    return container;
}



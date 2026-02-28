import grapesjs from "grapesjs";
import blocksBasic from "grapesjs-blocks-basic";
import presetWebpage from "grapesjs-preset-webpage";
import componentCodeEditor from "grapesjs-component-code-editor";
import "grapesjs/dist/css/grapes.min.css";
import "grapesjs-component-code-editor/dist/grapesjs-component-code-editor.min.css";

// Variable global para el editor
let grapesEditor;

document.addEventListener("DOMContentLoaded", () => {
    const editorContainer = document.getElementById("gjs");
    if (!editorContainer) {
        console.error("No se encontró el contenedor para GrapesJS");
        return;
    }
    grapesEditor = grapesjs.init({
        container: "#gjs",
        fromElement: true,
        height: "100%",
        width: "auto",
        storageManager: false,
        plugins: [blocksBasic, presetWebpage, componentCodeEditor],
    });

});
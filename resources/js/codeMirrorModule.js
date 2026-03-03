import CodeMirror from 'codemirror'
import 'codemirror/lib/codemirror.css'

// Lenguaje JavaScript
import 'codemirror/mode/javascript/javascript'

// Tema (opcional, estilo VSCode oscuro)
import 'codemirror/theme/material-darker.css'

function initEditors() {
    document.querySelectorAll('.editor-js').forEach((textarea) => {
        // Evitar inicializar dos veces el mismo textarea
        if (textarea.dataset.codemirrorInitialized) {
            return
        }

        textarea.dataset.codemirrorInitialized = 'true'

        CodeMirror.fromTextArea(textarea, {
            mode: 'javascript',
            theme: 'material-darker',
            lineNumbers: true,
            tabSize: 2,
            indentUnit: 2,
            autoCloseBrackets: true,
            matchBrackets: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete"
            }
        })
    })
}

// Inicializa en carga normal y tras navegaciones de Livewire
document.addEventListener('DOMContentLoaded', initEditors)
document.addEventListener('livewire:navigated', initEditors)
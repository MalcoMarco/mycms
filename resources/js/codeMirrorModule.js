import { EditorView, basicSetup } from 'codemirror'
import { javascript } from '@codemirror/lang-javascript'
import { oneDark } from '@codemirror/theme-one-dark'

function initEditors() {
    document.querySelectorAll('.editor-js').forEach((textarea) => {
        if (textarea.dataset.codemirrorInitialized) {
            return
        }

        textarea.dataset.codemirrorInitialized = 'true'

        const view = new EditorView({
            doc: textarea.value,
            extensions: [
                basicSetup,
                javascript(),
                oneDark,
                EditorView.updateListener.of((update) => {
                    if (update.docChanged) {
                        textarea.value = update.state.doc.toString()
                        textarea.dispatchEvent(new Event('input', { bubbles: true }))
                    }
                }),
            ],
            parent: textarea.parentElement,
        })

        textarea.style.display = 'none'
    })
}

// Inicializa en carga normal y tras navegaciones de Livewire
document.addEventListener('DOMContentLoaded', initEditors)
document.addEventListener('livewire:navigated', initEditors)
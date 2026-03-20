<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodeMirror | {{ $post->title }}</title>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 12px;
            background: #1e1e2e;
            border-bottom: 1px solid #333;
            height: 40px;
            box-sizing: border-box;
        }

        .toolbar-title {
            color: #cdd6f4;
            font: 600 14px/1 sans-serif;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-btn {
            padding: 6px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font: 600 13px/1 sans-serif;
            color: #fff;
            background: #2563eb;
        }

        .toolbar-btn:hover {
            background: #1d4ed8;
        }

        .toolbar-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .toolbar-btn-icon {
            padding: 5px 8px;
            background: #374151;
            color: #cdd6f4;
            font-size: 16px;
            line-height: 1;
        }

        .toolbar-btn-icon:hover {
            background: #4b5563;
        }

        .toolbar-btn-icon.active {
            background: #2563eb;
        }

        .toolbar-status {
            color: #a6adc8;
            font: 12px/1 sans-serif;
        }

        .editor-container {
            display: flex;
            height: calc(100vh - 40px);
            width: 100%;
        }

        .editor-container.vertical {
            flex-direction: column;
        }

        #codemirror-editor {
            flex: 1 1 50%;
            min-width: 100px;
            min-height: 100px;
            height: 100%;
            overflow: auto;
        }

        .vertical #codemirror-editor {
            height: auto;
        }

        .divider {
            flex: 0 0 6px;
            background: #333;
            cursor: col-resize;
            position: relative;
            z-index: 10;
            transition: background .15s;
        }

        .divider:hover,
        .divider.dragging {
            background: #2563eb;
        }

        .vertical .divider {
            cursor: row-resize;
        }

        #preview-container {
            flex: 1 1 50%;
            min-width: 100px;
            min-height: 100px;
            height: 100%;
            background: white;
        }

        .vertical #preview-container {
            height: auto;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Ajuste para que CodeMirror ocupe todo el alto */
        .cm-editor {
            height: 100%;
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <span class="toolbar-title">{{ $post->title }}</span>
        <div class="toolbar-actions">
            <button id="layout-horizontal" class="toolbar-btn toolbar-btn-icon active" title="Horizontal">&#9646;&#9646;</button>
            <button id="layout-vertical" class="toolbar-btn toolbar-btn-icon" title="Vertical">&#9866;&#9866;</button>
            <button id="format-btn" class="toolbar-btn" style="background:#7c3aed" title="Formatear código (Prettier)">Formatear</button>
            <span id="save-status" class="toolbar-status"></span>
            <button id="save-btn" class="toolbar-btn">Guardar</button>
        </div>
    </div>

    <script>
        window.post = {
            id: {{ $post->id }},
            slug: "{{ $post->slug }}",
            content: @json($post->content ?? ''),
        };
    </script>

    <div class="editor-container" id="editor-container">
        <div id="codemirror-editor"></div>
        <div class="divider" id="divider"></div>
        <div id="preview-container">
            <iframe id="preview-iframe"></iframe>
        </div>
    </div>
    @vite(['resources/js/codeMirrorPageBuilder.js'])
</body>

</html>

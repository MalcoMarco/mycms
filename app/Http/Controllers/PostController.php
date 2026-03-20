<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Show the page builder for a specific post.
     */
    public function pagebuilder($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $webSetting = $post->tenant->webSettings;

        return view('pages.tenants.page-builder', compact('post', 'webSetting'));
    }

    /**
     * Show the code mirror editor for a specific post.
     */
    public function codeEditor($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        return view('pages.tenants.page-builder-code-mirror', compact('post'));
    }

    /**
     * Show the preview for a specific post.
     */
    public function preview($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $webSetting = $post->tenant->webSettings;

        return view('pages.tenants.page-preview', compact('post', 'webSetting'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the body of content, CSS, and JS for a specific post.
     * Este método recibe el contenido HTML completo generado por GrapesJS, que incluye el <body> con sus atributos, así como el CSS y JS separados. El método extrae el contenido del <body> y lo reemplaza en la base de datos, preservando cualquier atributo que el <body> original pueda tener. Además, actualiza los campos de CSS y JS del post.
     */
    public function update(Request $request, $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $incomingHtml = $request->input('content', '');

        // Extraer solo el contenido del <body> del HTML recibido desde GrapesJS
        if (preg_match('/(<body[^>]*>)(.*)<\/body>/s', $incomingHtml, $bodyMatch)) {
            $newBodyTag = $bodyMatch[1]; // <body class="..."> con todos sus atributos
            $newBodyContent = $bodyMatch[2];

            // Reemplazar el body completo (tag + contenido) preservando atributos
            if (preg_match('/<body[^>]*>.*<\/body>/s', $post->content)) {
                $post->content = preg_replace('/<body[^>]*>.*<\/body>/s', $newBodyTag . $newBodyContent . '</body>', $post->content);
            } else {
                $post->content = $incomingHtml;
            }
        } else {
            $post->content = $incomingHtml;
        }

        $post->css = $request->input('css');
        $post->js = $request->input('js');
        $post->save();

        return response()->json(['message' => 'Content updated successfully']);
    }

    /**
     * Actualizar el contenido del post utilizando un editor de código como CodeMirror, donde el usuario edita directamente el HTML completo, incluyendo el <body> con sus atributos. Este método reemplaza todo el contenido del post con el HTML recibido, sin intentar extraer o preservar atributos específicos del <body>.
     */
    public function updateWithCodeMirror(Request $request, $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $incomingHtml = $request->input('content', '');

        $post->content = $incomingHtml;
        $post->save();

        return response()->json(['message' => 'Content updated successfully']);
    }
}

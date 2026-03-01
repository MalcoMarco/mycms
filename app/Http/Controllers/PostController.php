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
        return view('pages.tenants.page-builder', compact('post'));
    }

    /**
     * Show the preview for a specific post.
     */
    public function preview($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        return view('pages.tenants.page-preview', compact('post'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

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
     * Update the specified resource in storage.
     */
    public function update(Request $request, $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $post->content_body = $request->input('content_body');
        $post->content_css = $request->input('content_css');
        $post->cdns = $request->input('cdns');
        $post->save();
        return response()->json(['message' => 'Content updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}

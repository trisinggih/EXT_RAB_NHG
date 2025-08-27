<?php

namespace App\Http\Controllers;

use App\Models\Blogs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $blogs = Blogs::paginate(10);
        return Inertia::render('blog/Index', [
            'blogs' => $blogs
        ]);

    }

    public function create(): Response
    {
        return Inertia::render('blog/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title_blog' => 'required|string|max:255',
            'image_blog' => 'required|image|max:2048',
            'content_blog' => 'required|string|max:255',
        ]);

        if ($request->hasFile('image_blog')) {
            $image = $request->file('image_blog');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName);
            $data['image_blog'] = $imageName; 
        }
        Blogs::create($data);
        return redirect()->route('blogs.index')->with('message', 'Blog created successfully.');
    }

    public function edit(Blogs $blog): Response
    {
        return Inertia::render('blog/Edit', compact('blog'));
    }

    public function update(Request $request, Blogs $blog): RedirectResponse
    {
        $data = $request->validate([
            'title_blog' => 'required|string|max:255',
            'image_blog' => 'required|image|max:2048',
            'description_blog' => 'required|string|max:255',
        ]);
        $blog->update($data);
        return redirect()->route('blogs.index')->with('message', 'Blog updated successfully.');
    }

    public function destroy(Blogs $blog): RedirectResponse
    {
        $blog->delete();
        return redirect()->route('blogs.index')->with('message', 'Blog deleted successfully.');
    }
}

  
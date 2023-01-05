<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use function PHPSTORM_META\map;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(5);

        return view('posts/index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        // validate inputan user
        $this->validate($request, [
            'image' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048',
            'title' => 'required',
            'content' => 'required'
        ]);

        // store gambar ke folder public / posts
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // membuat post
        Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content
        ]);

        // return view ke index
        return redirect()->route('posts.index')->with(['success' => "Data berhasil disimpan" ]);
    }

    public function show($id)
    {
        $post = Post::find($id);

        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        // validasi inputan user
        $this->validate($request, [
            'image'     => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required|min:5',
            'content'   => 'required|min:10'
        ]);

        // check jika gambar sudah ada
        if ($request->hasFile('image')) {

            // upload gambar baru
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // hapus gambar lama
            Storage::delete('public/posts/'.$post->image);

            // upfate post dengan data baru
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content
            ]);

        } else {

            //update post tanpa gambar
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
        }

        // redirect ke index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    public function destroy(Post $post) {
        // hapus gambar
        Storage::delete('public/posts/' . $post->image);

        // hapus post
        $post->delete();

        // redirect ke index
        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}

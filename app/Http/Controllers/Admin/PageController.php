<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = Page::paginate(10);

        return view('admin.pages.home', ['pages' => $pages]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'title',
            'body'
        ]);

        $data['slug'] = Str::slug($data['title'], '-');

        $validator = $this->validatorCreate($data);

        if ($validator->fails()) {
            return redirect()->route('pages.create')->withErrors($validator)->withInput();
        }

        Page::create($data);

        return redirect()->route('pages.index')->with('warning', 'Página criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $page = Page::find($id);

        if (! $page) {
            return redirect()->route('pages.index')->with('warning', 'Página não encontrada!');
        }

        return view('admin.pages.edit', ['page' => $page]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->only([
            'title',
            'body'
        ]);

        $data['slug'] = '';

        $page = Page::find($id);

        if (! $page) {
            return redirect()->route('pages.edit', ['page' => $id])->with('warning', 'Página não encontrada!');
        }

        if (! empty($data['title'])) {
            $data['slug'] = Str::slug($data['title'], '-');
            $validator = $this->validatorUpdateTitle($data);
            if ($validator->fails()) {
                return redirect()->route('pages.edit', ['page' => $id])->withErrors($validator)->withInput();
            }
            
            $page->title = $data['title'];
        }

        if (! empty($data['slug']) && $data['slug'] != $page->slug) {
            $validator = $this->validatorUpdateSlug($data);
            if ($validator->fails()) {
                return redirect()->route('pages.edit', ['page' => $id])->withErrors($validator)->withInput();
            }

            $page->slug = $data['slug'];
        }


        if (! empty($data['body'])) {
            $validator = $this->validatorUpdateBody($data);
            if ($validator->fails()) {
                return redirect()->route('pages.edit', ['page' => $id])->withErrors($validator)->withInput();
            }

            $page->body = $data['body'];
        }

        $page->save();

        return redirect()->route('pages.index')->with('warning', 'Página atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $page = Page::find($id);

        if (! $page) {
            return redirect()->route('pages.index')->with('warning', 'Página não encontrada!');
        }

        $page->delete();

        return redirect()->route('pages.index')->with('warning', 'Página removida com sucesso!');
    }

    private function validatorCreate($data)
    {
        return Validator::make($data, [
            'title'     => ['required', 'string', 'max:100'],
            'body'      => ['string'],
            'slug'      => ['string', 'max:100', 'unique:pages']
        ]);

    }

    private function validatorUpdateTitle($data)
    {
        return Validator::make($data, [
            'title'     => ['string', 'max:100'],
        ]);

    }

    private function validatorUpdateSlug($data)
    {
        return Validator::make($data, [
            'slug'      => ['string', 'max:100', 'unique:pages']
        ]);

    }

    private function validatorUpdateBody($data)
    {
        return Validator::make($data, [
            'body'     => ['string'],
        ]);

    }
}

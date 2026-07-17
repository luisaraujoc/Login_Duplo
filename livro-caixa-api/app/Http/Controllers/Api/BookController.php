<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BookController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $books = $request->currentAccount()->books()->orderBy('number')->get();

        return BookResource::collection($books);
    }

    public function store(Request $request): BookResource
    {
        $data = $request->validate([
            'number' => ['required', 'integer', 'min:1'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        $book = $request->currentAccount()->books()->create($data);

        return new BookResource($book);
    }

    public function show(Request $request, Book $book): BookResource
    {
        $this->authorizeAccount($request, $book);

        return new BookResource($book);
    }

    public function update(Request $request, Book $book): BookResource
    {
        $this->authorizeAccount($request, $book);

        $data = $request->validate([
            'number' => ['required', 'integer', 'min:1'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        $book->update($data);

        return new BookResource($book);
    }

    public function destroy(Request $request, Book $book): Response
    {
        $this->authorizeAccount($request, $book);

        abort_if(
            $book->movements()->exists(),
            422,
            'Este livro possui lançamentos e não pode ser apagado.'
        );

        $book->delete();

        return response()->noContent();
    }

    private function authorizeAccount(Request $request, Book $book): void
    {
        abort_unless($book->account_id === $request->currentAccount()->id, 404);
    }
}

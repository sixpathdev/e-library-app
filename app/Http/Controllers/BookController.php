<?php

namespace App\Http\Controllers;

use App\Book;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JD\Cloudder\Facades\Cloudder as Cloudder;
use stdClass;

class BookController extends Controller
{
    public function allBooks()
    {
        $books = Book::all();

        $message = "Books fetched successfully";
        $status = http_response_code();
        return response()->json(compact('status', 'message', 'books'), 200);
    }

    public function userBooks($user_id)
    {
        $userbooks = Book::where('uploaded_by', $user_id)->get();

        $message = "User books fetched successfully";
        $status = http_response_code(200);
        return response()->json(compact('status', 'message', 'userbooks'), 200);
    }

    public function uploadbook(Request $request)
    {
        $this->validate($request, [
            'book' => 'required|mimes:pdf,doc,docx'
        ]);

        try {
            if ($request->hasFile('book') && $request->file('book')->isValid()) {
                $doc_name = $request->file('book')->getRealPath();
                $cloudder = Cloudder::upload($doc_name, null, array('folder' => 'books-api/books', "overwrite" => TRUE));
                $result = $cloudder->getResult();

                $book = new Book();
                $book->title = $request->input('title');
                $book->uploaded_by = Auth::user()->id;
                $book->description = $request->input('description');
                $book->size = $result['bytes'] / 1000;
                $book->department = $request->input('department');
                $book->link = $result['url'];
                $book->public_id = $result['public_id'];
                $book->pages = $result['pages'];
                $book->format = $result['format'];
                $book->save();

                $message = "Document uploaded successfully";
                $status = http_response_code(201);
                return response()->json(compact('status', 'message', 'book'), 201);
            } else {
                $message = "error uploading file";
                $status = http_response_code(400);
                return response()->json(compact('status', 'message'), 400);
            }
        } catch (Exception $e) {
            throw new Error($e);
        }
    }

    public function showbook($id)
    {
        try {
            $book = Book::findOrFail($id);

            $message = "Book data fetched successfully";
            $status = http_response_code(200);
            return response()->json(compact('status', 'message', 'book'), 200);
        } catch (Exception $e) {
            throw new Error($e);
        }
    }

    public function deletebook($id)
    {
        $getbook = Book::findOrFail($id);
        if ($getbook->uploaded_by == Auth::user()->id) {
            try {
                if (Cloudder::destroy($getbook->public_id)) {
                    $getbook->delete();
                    $message = "Book deleted successfully";
                    $status = http_response_code(200);
                    return response()->json(compact('status', 'message'), 200);
                }
            } catch (Exception $e) {
                throw new Error($e);
            }
        }
    }
}

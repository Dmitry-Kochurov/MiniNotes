<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Models\Note;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(title="Notes API", version="1.0.0")
 */
class NoteController extends Controller {

    /**
     * @OA\Get(path="/api/notes", tags={"Notes"}, summary="Получить все заметки",
     *     @OA\Response(response=200, description="Успешно")
     * )
     */
    public function index(): JsonResponse {
        return response()->json(Note::latest()->get());
    }

    /**
     * @OA\Post(path="/api/notes", tags={"Notes"}, summary="Создать заметку",
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"title","content"}, @OA\Property(property="title", type="string"), @OA\Property(property="content", type="string"))),
     *     @OA\Response(response=201, description="Создано")
     * )
     */
    public function store(StoreNoteRequest $request): JsonResponse {
        $note = Note::create($request->validated());
        return response()->json($note, 201);
    }

    /**
     * @OA\Get(path="/api/notes/{id}", tags={"Notes"}, summary="Посмотреть конкретную заметку",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Успешно"),
     *     @OA\Response(response=404, description="Не найдено")
     * )
     */
    public function show(Note $note): JsonResponse {
        return response()->json($note);
    }

    /**
     * @OA\Put(path="/api/notes/{id}", tags={"Notes"}, summary="Редактировать заметку",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(@OA\Property(property="title", type="string"), @OA\Property(property="content", type="string"))),
     *     @OA\Response(response=200, description="Обновлено")
     * )
     */
    public function update(StoreNoteRequest $request, Note $note): JsonResponse {
        $note->update($request->validated());
        return response()->json($note);
    }

    /**
     * @OA\Delete(path="/api/notes/{id}", tags={"Notes"}, summary="Удалить заметку",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Удалено")
     * )
     */
    public function destroy(Note $note): JsonResponse {
        $note->delete();
        return response()->json(null, 204);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase; // Автоматически очищает базу данных перед каждым тестом

    /**
     * Тест: Успешное создание заметки через API
     */
    public function test_can_create_note()
    {
        $data = [
            'title' => 'Тестовый заголовок',
            'content' => 'Текст тестовой заметки'
        ];

        $response = $this->postJson('/api/notes', $data);

        // Проверяем, что сервер вернул статус 201 (Created) и верную JSON-структуру
        $response->assertStatus(201)
            ->assertJsonFragment($data)
            ->assertJsonStructure(['id', 'title', 'content', 'created_at', 'updated_at']);

        // Проверяем, что запись физически появилась в базе данных
        $this->assertDatabaseHas('notes', $data);
    }

    /**
     * Тест: Валидация данных работает корректно (ошибка при пустых полях)
     */
    public function test_create_note_validation_fails_for_empty_fields()
    {
        $response = $this->postJson('/api/notes', []);

        // Ожидаем статус 422 (Unprocessable Entity) и ошибки для конкретных полей
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    /**
     * Тест: Получение списка всех заметок
     */
    public function test_can_get_all_notes()
    {
        // Напрямую создаем две записи в базе данных
        Note::create(['title' => 'Заметка 1', 'content' => 'Содержимое 1']);
        Note::create(['title' => 'Заметка 2', 'content' => 'Содержимое 2']);

        $response = $this->getJson('/api/notes');

        $response->assertStatus(200)
            ->assertJsonCount(2); // Проверяем, что в массиве ровно 2 элемента
    }

    /**
     * Тест: Редактирование существующей заметки
     */
    public function test_can_update_note()
    {
        $note = Note::create(['title' => 'Старый заголовок', 'content' => 'Старый текст']);

        $updatedData = [
            'title' => 'Новый заголовок',
            'content' => 'Новый текст'
        ];

        // ИСПРАВЛЕНО: Стрелочка вместо точки
        $response = $this->putJson("/api/notes/{$note->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment($updatedData);

        $this->assertDatabaseHas('notes', $updatedData);
    }

    /**
     * Тест: Успешное удаление заметки
     */
    public function test_can_delete_note()
    {
        $note = Note::create(['title' => 'Удалить меня', 'content' => 'Текст']);

        // ИСПРАВЛЕНО: Стрелочка вместо точки
        $response = $this->deleteJson("/api/notes/{$note->id}");

        $response->assertStatus(204); // Статус "No Content" при успешном удалении
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }
}

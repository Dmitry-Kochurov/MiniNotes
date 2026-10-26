<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мини-сервис заметок</title>
    <style>
        /* Базовые стили и сетка */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
            background: #f4f6f9;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        /* Красивая вертикальная форма */
        form {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 35px;
        }
        form h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.3rem;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
        }
        input, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        /* Блок кнопок управления */
        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        button {
            background: #3182ce;
            color: white;
            padding: 11px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        button:hover { background: #2b6cb0; }
        .btn-cancel { background: #718096; }
        .btn-cancel:hover { background: #4a5568; }

        /* Сетка для вывода карточек заметок */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        }
        .card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #1a202c;
            font-size: 1.2rem;
        }
        .card p {
            color: #4a5568;
            white-space: pre-line;
            margin: 0;
            line-height: 1.5;
        }

        /* Действия с заметками */
        .actions {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #edf2f7;
            display: flex;
            gap: 8px;
        }
        .btn-edit { background: #ecc94b; color: #1a202c; }
        .btn-edit:hover { background: #d69e2e; }
        .btn-delete { background: #e53e3e; }
        .btn-delete:hover { background: #c53030; }
    </style>
</head>
<body>
<h1>📝 Мои заметки</h1>

<form id="noteForm">
    <h2 id="formTitle">Создать новую заметку</h2>
    <input type="hidden" id="editingId" value="">

    <div class="form-group">
        <label for="titleInput">Заголовок:</label>
        <input id="titleInput" required />
    </div>

    <div class="form-group">
        <label for="contentInput">Текст заметки:</label>
        <textarea id="contentInput" rows="4" required></textarea>
    </div>

    <div class="btn-container">
        <button type="submit" id="submitBtn">Добавить заметку</button>
        <button type="button" id="cancelBtn" class="btn-cancel" style="display:none;">Отмена</button>
    </div>
</form>

<div id="notesContainer" class="grid">
    <p style="text-align:center; color:#718096; grid-column: 1/-1;">Загрузка заметок...</p>
</div>

<script>
    const noteForm = document.getElementById('noteForm');
    const formTitle = document.getElementById('formTitle');
    const editingId = document.getElementById('editingId');
    const titleInput = document.getElementById('titleInput');
    const contentInput = document.getElementById('contentInput');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const notesContainer = document.getElementById('notesContainer');

    async function fetchNotes() {
        try {
            const res = await fetch('/api/notes');
            const notes = await res.json();
            renderNotes(notes);
        } catch (err) {
            notesContainer.innerHTML = '<p style="color:#e53e3e; text-align:center; grid-column: 1/-1;">Ошибка соединения с API бэкенда</p>';
        }
    }

    function renderNotes(notes) {
        if (notes.length === 0) {
            notesContainer.innerHTML = '<p style="color:#718096; text-align:center; grid-column: 1/-1;">Заметок пока нет. Добавьте первую!</p>';
            return;
        }
        notesContainer.innerHTML = notes.map(note => `
                <div class="card">
                    <div>
                        <h3>${escapeHtml(note.title)}</h3>
                        <p>${escapeHtml(note.content)}</p>
                    </div>
                    <div class="actions">
                        <button onclick="editNote(${note.id}, '${escapeJs(note.title)}', '${escapeJs(note.content)}')" class="btn-edit">Ред.</button>
                        <button onclick="deleteNote(${note.id})" class="btn-delete">Удалить</button>
                    </div>
                </div>
            `).join('');
    }

    noteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = editingId.value;
        const data = { title: titleInput.value, content: contentInput.value };
        await fetch(id ? `/api/notes/${id}` : '/api/notes', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        });
        resetForm();
        fetchNotes();
    });

    window.editNote = (id, title, content) => {
        editingId.value = id;
        titleInput.value = title;
        contentInput.value = content;
        formTitle.innerText = 'Редактировать заметку';
        submitBtn.innerText = 'Сохранить';
        cancelBtn.style.display = 'inline-block';
    };

    window.deleteNote = async (id) => {
        if (confirm('Удалить заметку?')) {
            await fetch(`/api/notes/${id}`, { method: 'DELETE' });
            fetchNotes();
        }
    };

    cancelBtn.addEventListener('click', resetForm);
    function resetForm() {
        editingId.value = '';
        titleInput.value = '';
        contentInput.value = '';
        formTitle.innerText = 'Создать новую заметку';
        submitBtn.innerText = 'Добавить заметку';
        cancelBtn.style.display = 'none';
    }
    function escapeHtml(str) { return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"); }
    function escapeJs(str) { return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\n/g, '\\n'); }
    fetchNotes();
</script>
</body>
</html>

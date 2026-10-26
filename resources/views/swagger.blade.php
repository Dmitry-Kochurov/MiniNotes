<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Документация Notes API (Автоматизированная)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 20px; background: #fafafa; color: #3b4151; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; font-size: 2.5rem; color: #2c3e50; border-bottom: 3px solid #34495e; padding-bottom: 10px; }
        .desc { color: #7f8c8d; font-size: 1.1rem; margin-bottom: 30px; }
        .endpoint { margin-bottom: 25px; border: 1px solid #d9d9d9; border-radius: 4px; overflow: hidden; background: #fff; }
        .header { display: flex; align-items: center; padding: 10px 20px; cursor: pointer; user-select: none; font-weight: bold; font-family: monospace; font-size: 1.1rem; }
        .method { padding: 6px 12px; border-radius: 3px; color: white; font-size: 0.8rem; font-weight: bold; min-width: 60px; text-align: center; margin-right: 15px; }

        /* Цветовая схема методов как в оригинальном Swagger UI */
        .get-end { border-color: #61afff; } .get-end .header { background: rgba(97, 175, 255, 0.1); } .get-end .method { background: #61afff; }
        .post-end { border-color: #49cc90; } .post-end .header { background: rgba(73, 204, 144, 0.1); } .post-end .method { background: #49cc90; }
        .put-end { border-color: #fca130; } .put-end .header { background: rgba(252, 161, 48, 0.1); } .put-end .method { background: #fca130; }
        .delete-end { border-color: #f93e3e; } .delete-end .header { background: rgba(249, 62, 62, 0.1); } .delete-end .method { background: #f93e3e; }

        .path { color: #3b4151; flex-grow: 1; }
        .summary { color: #7f8c8d; font-size: 0.9rem; font-weight: normal; }
        .content { display: none; padding: 20px; border-top: 1px solid #d9d9d9; background: #fafafa; }
        .section-title { font-weight: bold; margin: 15px 0 5px 0; font-size: 0.95rem; color: #2c3e50; }
        pre { background: #2d3748; color: #fff; padding: 15px; border-radius: 6px; overflow-x: auto; margin: 5px 0; font-family: monospace; font-size: 0.9rem; }
        .badge { background: #e2e8f0; color: #4a5568; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-family: monospace; }
    </style>
</head>
<body>
<div class="container">
    <h1 id="apiTitle">Загрузка API...</h1>
    <p class="desc" id="apiDesc"></p>
    <div id="endpointsContainer"></div>
</div>

<script>
    // Инициализируем спецификацию: Laravel автоматически рендерит JSON объект напрямую из файла ресурсов
    const openApiSpec = {!! file_get_contents(resource_path('swagger.json')) !!};

    document.getElementById('apiTitle').innerText = openApiSpec.info.title + " (Interactive Docs)";
    document.getElementById('apiDesc').innerText = openApiSpec.info.description;

    const container = document.getElementById('endpointsContainer');
    let html = '';

    // Динамически парсим пути (Paths) и генерируем автоматизированный интерактивный интерфейс
    Object.keys(openApiSpec.paths).forEach(pathKey => {
        const pathObj = openApiSpec.paths[pathKey];

        Object.keys(pathObj).forEach(method => {
            const operation = pathObj[method];
            const methodClass = `${method}-end`;

            // Извлекаем параметры валидации/тела запроса, если они есть
            let requestBodyHtml = '';
            if (operation.requestBody) {
                const schema = operation.requestBody.content['application/json'].schema;
                requestBodyHtml = `
                        <div class="section-title">Схема тела запроса (Передаваемый JSON):</div>
                        <pre>${JSON.stringify(schema, null, 2)}</pre>
                    `;
            }

            // Извлекаем параметры пути (например, {id})
            let paramsHtml = '';
            if (operation.parameters) {
                paramsHtml = `<div class="section-title">Параметры пути:</div><div style="margin-bottom:10px;">`;
                operation.parameters.forEach(p => {
                    paramsHtml += `<span class="badge">${p.in}: ${p.name} (${p.schema.type})</span> — ${p.required ? 'обязательный' : ''}`;
                });
                paramsHtml += `</div>`;
            }

            html += `
                    <div class="endpoint ${methodClass}">
                        <div class="header" onclick="toggleEndpoint(this)">
                            <span class="method">${method.toUpperCase()}</span>
                            <span class="path">${pathKey}</span>
                            <span class="summary">${operation.summary || ''}</span>
                        </div>
                        <div class="content">
                            ${paramsHtml}
                            ${requestBodyHtml}
                            <div class="section-title">Возможные ответы сервера (Responses):</div>
                            <pre>${JSON.stringify(operation.responses, null, 2)}</pre>
                        </div>
                    </div>
                `;
        });
    });

    container.innerHTML = html;

    // Метод сворачивания/разворачивания блоков спецификации при клике
    function toggleEndpoint(element) {
        const content = element.nextElementSibling;
        content.style.display = content.style.display === 'block' ? 'none' : 'block';
    }
</script>
</body>
</html>

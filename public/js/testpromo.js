async function testPromo() {
    // Генерируем случайные данные
    const phone = '+7' + Math.floor(1000000000 + Math.random() * 9000000000);
    const name = 'User_' + Math.floor(Math.random() * 10000);

    try {
        const response = await fetch('../src/test.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone, name })
        });

        // Проверяем Content-Type перед парсингом
        const contentType = response.headers.get('content-type');

        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('💥 Сервер вернул не JSON:', text.substring(0, 200));
            throw new Error(`Сервер вернул ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('✅ Ответ сервера:', result);

        if (result.success) {
            alert(`Пользователь добавлен!\nID: ${result.id}\nИмя: ${name}\nТелефон: ${phone}`);
        } else {
            alert('❌ Ошибка: ' + (result.message || 'неизвестно'));
        }
    } catch (err) {
        console.error('💥 Ошибка запроса:', err);
        alert('Не удалось подключиться к серверу: ' + err.message);
    }
}
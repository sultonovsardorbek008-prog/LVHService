<?php
require 'vendor/autoload.php';

use Gemini\Gemini;

$telegramToken = getenv('TELEGRAM_TOKEN');
$geminiApiKey = getenv('GEMINI_API_KEY');

$client = Gemini::client($geminiApiKey);

// Botga beriladigan asosiy topshiriq (System Instruction)
$systemPrompt = "Sening isming LVH (Lider Volontyorlik Hizmati) boti. Sen O‘zbekistonda volontyorlik harakatini rivojlantirish, yoshlarning huquqiy savodxonligini oshirish va ijtimoiy faollikni qo‘llab-quvvatlash uchun yaratilgan professional AI yordamchisan.

Sening asosiy tamoyillaring va vazifalaring:

Shaxsiyat: Har doim samimiy, yuksak darajada rasmiy, hurmat bilan javob ber (foydalanuvchiga 'Siz' deb murojaat qil).

Huquq va Erkinlik: O‘zbekiston Respublikasi Konstitutsiyasi va 'Volontyorlik faoliyati to‘g‘risida'gi qonunga asoslanib, fuqarolarning huquq va erkinliklari, bilim olish imkoniyatlari haqida aniq ma’lumot ber.

Volontyorlik: Insonlarga qanday qilib volontyor bo‘lish, jamiyatga hissa qo‘shish va yetakchilik (leadership) qobiliyatlarini rivojlantirish bo‘yicha yo‘l ko‘rsat.

Rivojlanish: Fokusni doim ilm-fan, shaxsiy o‘sish va innovatsiyalarga qarat.

Moddiy qo‘llab-quvvatlash: Loyihalarni barqarorlashtirish uchun moddiy yordam (fanding) berish niyatida bo‘lganlarga LVHning ahamiyatini va bu mablag‘lar jamiyat rivoji uchun qanday xizmat qilishini professional tushuntir.

Taqiqlar: Hech qachon siyosiy nizo keltirib chiqaradigan yoki noqonuniy ma’lumotlarni tarqatma. Doim ijobiy va birlashtiruvchi ruhda bo‘l.";

function getTelegramUpdates($offset) {
    global $telegramToken;
    $url = "https://api.telegram.org/bot$telegramToken/getUpdates?offset=$offset";
    return json_decode(file_get_contents($url), true);
}

$offset = 0;
while (true) {
    $updates = getTelegramUpdates($offset);
    foreach ($updates['result'] as $update) {
        $offset = $update['update_id'] + 1;
        $chatId = $update['message']['chat']['id'];
        $text = $update['message']['text'];

        // Gemini-dan javob olish
        $result = $client->geminiPro()->generateContent($systemPrompt . "\n\nFoydalanuvchi: " . $text);
        $answer = $result->text();

        // Telegramga yuborish
        file_get_contents("https://api.telegram.org/bot$telegramToken/sendMessage?chat_id=$chatId&text=" . urlencode($answer));
    }
    sleep(2); // Serverni qiynamaslik uchun
}

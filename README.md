📝 Product Import Command – Prompt (TR & EN)
🇹🇷 Türkçe Açıklama

Bu proje için bir yapay zekâ kod asistanına (ChatGPT, Claude veya GitHub Copilot gibi) verilmek üzere bir komut istemi (prompt) hazırlanmıştır. Amaç, üçüncü taraf bir API’den ürün verilerini içe aktaran, basit ama doğru çalışan bir Laravel Artisan komutu oluşturmaktır. Komutun üretime hazır, temiz ve anlaşılır bir yapıda olması hedeflenmiştir.

Aşağıdaki prompt, yapay zekânın doğru kodu ilk denemede oluşturmasını sağlayacak şekilde ayrıntılı olarak hazırlanmıştır:

✔️ PROMPT (Türkçe)

Laravel için basit ama düzgün çalışan bir Artisan komutu yazmanı istiyorum. Komutun amacı: bir API’den ürünleri içe aktarmak. Çok profesyonel bir mimari istemiyorum, sadece anlaşılır, açıklayıcı ve düzgün çalışan bir kod yeterli. Gereksinimler şöyle:

API sayfa başına 100 ürün döndürüyor (varsayılan böyle olsun)

Dakikada en fazla 10 istek yapabilirim (basit sleep() yeterli)

Komut import:products şeklinde çalışmalı

--page ve --limit parametreleri olmalı (ör: --page=1 --limit=3)

Progress bar olsun

Her ürün için basit doğrulama yap (id, title, price gibi)

Hatalı ürün olursa sayfa işlemini bırakmadan devam et

Hataları storage/logs içine logla

Basit bir retry mekanizması olsun (ör. 2–3 tekrar denemesi)

Transaction + rollback kullan (sayfa içindeki işlemler toplu işlensin)

Kod temiz ve anlaşılır olsun, aşırı soyutlama istemiyorum

Kod tek dosyada da olabilir veya istersen basit bir service class kullanabilirsin

İstersen basit bir model + migration örneği ekleyebilirsin, ama karmaşıklaştırma

Amacım: İlk denemede sorunsuz çalışan sade bir import komutu elde etmek

🇬🇧 English Description

This repository includes a detailed prompt created for an AI coding assistant (ChatGPT, Claude, GitHub Copilot, etc.) to generate a clean and functional Laravel Artisan command. The command’s purpose is to import product data from a third-party API. The prompt is designed to ensure that the AI produces working, production-ready code on the first attempt, without unnecessary architectural complexity.

✔️ PROMPT (English)

I want you to write a simple but fully functional Laravel Artisan command. The goal is to import products from a third-party API. I don’t want an overly professional architecture — just clean, understandable, and working code. Requirements are:

API returns 100 items per page (assume this as default)

API rate-limit: max 10 requests per minute (simple sleep() is fine)

The command should run as: import:products

It must support --page and --limit parameters (e.g. --page=1 --limit=3)

Include a progress bar

Validate each product (e.g., id, title, price)

If a product is invalid, continue without stopping the page processing

Log invalid products into storage/logs

Include a simple retry mechanism (2–3 attempts before failing)

Use transaction + rollback so each page is processed atomically

Code should be clean and readable; no heavy abstractions needed

The command can be a single file, or optionally use a small service class

You may include a simple model + migration example but keep it minimal

Goal: A clean, reliable import command that works correctly on the first try

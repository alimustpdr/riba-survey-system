<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RİBA V2 (Deneme)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: radial-gradient(900px 600px at 10% 10%, rgba(99,102,241,0.20), transparent 60%),
                  radial-gradient(900px 600px at 90% 20%, rgba(34,197,94,0.16), transparent 60%),
                  linear-gradient(180deg, #0b1220 0%, #0f172a 100%);
      color: #e5e7eb;
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 24px;
    }
    .cardx {
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.10);
      border-radius: 18px;
      max-width: 760px;
      width: 100%;
    }
    .muted { color: rgba(229,231,235,0.75); }
    .btn-grad { background: linear-gradient(135deg, #6366f1 0%, #22c55e 100%); border: none; }
    .btn-grad:hover { filter: brightness(1.05); }
    code { color: #a5b4fc; }
  </style>
</head>
<body>
  <div class="cardx p-4 p-md-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
      <div>
        <h1 class="h3 mb-2">RİBA V2 (Deneme Alanı)</h1>
        <div class="muted">
          Bu sayfa yeni mimariyi sıfırdan kurmak için <strong>izole</strong> bir başlangıç noktasıdır.
          Mevcut sistem silinmez; üretim güvenliği korunur.
        </div>
      </div>
      <div class="text-end">
        <div class="muted small mb-2">Hızlı linkler</div>
        <div class="d-grid gap-2">
          <a class="btn btn-grad" href="/modern/">Modern UI</a>
          <a class="btn btn-outline-light" href="/index.php?classic=1">Klasik Ana Sayfa</a>
        </div>
      </div>
    </div>

    <hr class="border-white border-opacity-10 my-4">

    <h2 class="h5 mb-2">Ne yapacağız?</h2>
    <ol class="muted mb-4">
      <li>V2 ekranlarını bu klasörde (`/v2`) adım adım geliştireceğiz.</li>
      <li>Hazır olunca domain kökünü V2’ye yönlendireceğiz (CyberPanel tarafında).</li>
      <li>Mevcut anket/kurulum/data yapısı korunacak; gerekiyorsa yeni tablolar additive şekilde eklenecek.</li>
    </ol>

    <div class="alert alert-warning mb-0" style="background: rgba(245,158,11,0.10); border-color: rgba(245,158,11,0.25); color:#fde68a;">
      <strong>Not:</strong> “Her şeyi silip baştan başlatmak” yerine bu yaklaşım güvenli.
      Çünkü canlı veriyi ve mevcut RİBA tanımlarını riske atmıyoruz.
    </div>
  </div>
</body>
</html>


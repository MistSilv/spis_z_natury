<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Spis z natury - {{ $spis->name }}</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    @media print {
      @page {
        size: A4 portrait;
        margin: 5mm 5mm 10mm 5mm; 
        
        @bottom-center {
          content: counter(page) " / " counter(pages);
          font-size: 8px;
          color: black; 
          margin-bottom: 5mm; 
        }
      }

      body {
        margin: 0;
        padding: 0;
        counter-reset: page;
      }

      .no-print {
        display: none !important;
      }

      table {
        page-break-inside: auto;
      }

      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }

      thead {
        display: table-header-group;
      }

      .page-footer {
        display: none;
      }

      .avoid-page-break {
        page-break-inside: avoid;
      }

      tfoot {
        display: none;
      }
    }

    body {
      background: white;
      color: black;
      font-family: sans-serif;
      font-size: 12px;
      padding: 24px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
    }

    th, td {
      border: 1px solid black;
      padding: 2px 4px;
    }

    .summary-row {
      background-color: #f3f4f6;
      font-weight: 600;
    }
  </style>
</head>

<body class="p-6 bg-white">

  <div class="no-print flex justify-end mb-4">
    <button onclick="window.print()" class="bg-sky-800 text-white px-4 py-2 rounded hover:bg-sky-600 transition">
        Drukuj / Zapisz PDF
    </button>
  </div>

  <div class="mb-4">
    <p class="leading-tight text-sm">
      Jarosławdis Sp. z o.o<br>
      ul. Wybrzeże Kościuszkowskie 45/79<br>
      00-347 Warszawa<br>
      NIP 5252675059
    </p>
  </div>

  <h1 class="text-center text-lg font-semibold mb-4">
    Arkusz spisu z natury nr {{ $spis->name ?? '—' }}
  </h1>

  <table class="mb-4">
    <tbody>
      <tr>
        <td class="w-2/5 border-none">Nazwa lub numer pola spisowego</td>
        <td class="w-3/5 font-medium border-none">
          {{ $spis->region->name ?? 'Brak regionu' }} surowce
        </td>
      </tr>
      <tr>
        <td class="border-none">Przedmiot spisu</td>
        <td class="font-medium border-none">
          {{ $spis->region->name ?? '—' }}
        </td>
      </tr>
      <tr>
        <td class="border-none">Osoba odpowiedzialna materialnie</td>
        <td class="border-none">....................................................</td>
      </tr>
    </tbody>
  </table>

  <div class="flex justify-between mb-4">
    <div class="flex-1">
      <p>Skład zespołu spisowego:<br>
      ....................................................<br>
      ....................................................<br>
      ....................................................</p>
    </div>
    <div class="flex-1"></div>
    <div class="flex-1 text-right">
      <p>Inne osoby obecne przy spisie:<br>
      ....................................................<br>
      ....................................................<br>
      ....................................................</p>
    </div>
  </div>

  <div class="text-center font-medium mb-4">
    Spis z natury na dzień: {{ $date }}
  </div>

  @php
  $unitShortcuts = [
      'sztuka' => 'szt',
      'kilogram' => 'kg',
      'gram' => 'g',
      'litr' => 'l',
      'mililitr' => 'ml',
      'opakowanie' => 'opk',
      'tabliczka' => 'tab',
      'butelka' => 'but',
      'karton' => 'kart',
      'paczka' => 'pacz',
  ];
  @endphp
  
  <table class="text-[11px]">
    <thead>
      <tr class="bg-gray-200 text-left">
        <th class="w-[4%]">Poz.</th>
        <th class="w-[36%]">Towar</th>
        <th class="w-[15%]">EAN</th>
        <th class="w-[5%]">Jm</th>
        <th class="w-[8%] text-right">Ilość</th>
        <th class="w-[9%] text-right">Cena jedn.</th>
        <th class="w-[10%] text-right">Wartość</th>
      </tr>
    </thead>

    <tbody>
      @foreach($products as $index => $product)
        <tr>
          <td class="text-right">{{ $index + 1 }}</td>
          <td>{{ $product->name }}</td>
          <td class="text-right">{{ $product->ean }}</td>
          <td>{{ $unitShortcuts[$product->unit] ?? $product->unit }}</td>
          <td class="text-right">{{ number_format($product->quantity, 2) }}</td>
          <td class="text-right">{{ number_format($product->unit_price, 2) }}</td>
          <td class="text-right">{{ number_format($product->total_value, 2) }}</td>
        </tr>
      @endforeach

      <tr class="summary-row">
        <td colspan="6" class="border-none"></td>
        <td class="text-right">{{ number_format($products->sum('total_value'), 2) }}</td>
      </tr>
    </tbody>

    <tfoot style="display: none;">
      <tr class="bg-gray-100 font-semibold">
        <td colspan="6" class="text-right">Razem:</td>
        <td class="text-right">{{ number_format($products->sum('total_value'), 2) }}</td>
      </tr>
    </tfoot>
  </table>

  <div class="mt-6 flex justify-between avoid-page-break">
    <div>
      <p>Spis zakończono na pozycji: {{ $products->count() }}</p>
      <p class="mt-1">Podpisy członków zespołu spisowego:</p>
    </div>
    <div class="text-right">
      <p class="mt-6">Podpis osoby odpowiedzialnej materialnie</p>
    </div>
  </div>

</body>
</html>
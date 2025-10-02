<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Spis z natury - {{ $spis->name }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; }
    @page {
        margin: 5mm;
    }
  </style>
</head>
<body>

<div class="main-container" style="width: 100%;">

    <div class="main-one"> 
        <p>Jarosławdis Sp. z o.o<br>
        ul. Wybrzeże Kościuszkowskie 45/79<br>
        00-347 Warszawa<br>
        NIP 5252675059</p>
    </div>

    <div class="main-two" style="text-align: center;">
        <p>Arkusz spisu z natury nr {{ $spis->number ?? '22/2025/SUR' }}</p>
    </div>

    <div class="main-three" style="margin-bottom: 8px;">
      <table style="width: 100%; border: none; border-collapse: collapse;">
        <tr>
          <td style="width: 40%; border: none;">Nazwa lub numer pola spisowego</td>
          <td style="width: 60%; border: none;">{{ $spis->field_name ?? 'Garmaż surowce' }}</td>
        </tr>
        <tr>
          <td style="border: none;">Przedmiot spisu</td>
          <td style="border: none;">{{ $spis->subject ?? 'Garmaż' }}</td>
        </tr>
        <tr>
          <td style="border: none;">Osoba odpowiedzialna materialnie</td>
          <td style="border: none;">....................................................</td>
        </tr>
      </table>
    </div>

    <div style="display: table; width: 100%;">
      <div style="display: table-row;">
        <div style="display: table-cell; width: 33%;">
          <p>Skład zespołu spisowego:<br> 
          ____________________________<br>
          ____________________________<br>
          ____________________________</p>
        </div>
        <div style="display: table-cell; width: 33%;"></div>
        <div style="display: table-cell; width: 33%;=">
          <p>Inne osoby obecne przy spisie:<br>
          ____________________________<br>
          ____________________________<br>
          ____________________________</p>
        </div>
      </div>
    </div>

    <div class="main-five" style="text-align: center;">
        <p>Spis z natury na dzień: {{ $date }}</p>
    </div>
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

<div class="table-wrapper" style="margin-top: 10px;">
  <table style="width: 100%; border-collapse: collapse; font-size: 10px; text-align: left;">
    <thead>
      <tr>
        <th style="width: 4%; border: 1px solid #000; background: #d9d9d9; text-align: left; padding-left: 3px;">Poz.</th>
        <th style="width: 36%; border: 1px solid #000; background: #d9d9d9; text-align: left; padding-left: 3px;">Towar</th>
        <th style="width: 15%; border: 1px solid #000; background: #d9d9d9; text-align: left; padding-left: 3px;">EAN</th>
        <th style="width: 5%; border: 1px solid #000; background: #d9d9d9; text-align: left; padding-left: 3px;">Jm</th>
        <th style="width: 8%; border: 1px solid #000; background: #d9d9d9; text-align: left; padding-left: 3px;">Ilość</th>
        <th style="width: 9%; border: 1px solid #000; background: #d9d9d9; text-align: left; padding-left: 3px;">Cena jedn.</th>
        <th style="width: 10%; border: 1px solid #000; background: #d9d9d9; text-align: left; padding-left: 3px;">Wartość</th>
      </tr>
    </thead>
    <tbody>
      @foreach($products as $index => $product)
        <tr>
          <td style="border: 1px solid #000; text-align: right; padding-right: 3px;">{{ $index + 1 }}</td>
          <td style="border: 1px solid #000; text-align: left; padding-left: 3px;">{{ $product->name }}</td>
          <td style="border: 1px solid #000; text-align: right; padding-right: 3px;">{{ $product->ean }}</td>
          <td style="border: 1px solid #000; text-align: left; padding-left: 3px;">
              {{ $unitShortcuts[$product->unit] ?? $product->unit }}
          </td>
          <td style="border: 1px solid #000; text-align: right; padding-right: 3px;">{{ number_format($product->quantity, 2) }}</td>
          <td style="border: 1px solid #000; text-align: right; padding-right: 3px;">{{ number_format($product->unit_price, 2) }}</td>
          <td style="border: 1px solid #000; text-align: right; padding-right: 3px;">{{ number_format($product->total_value, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="6" style="border: 0px solid #000;"></td>
        <td style="border: 1px solid #000; text-align: right;">
        {{ number_format($products->sum('total_value'), 2) }}
        </td>
    </tr>
    </tfoot>
  </table>
</div>


<table style="width: 100%; margin-top: 10px;">
    <th></th>
    <th></th>
    <tr>
        <td>Spis zakończono na pozycji: {{ $products->count() }} </td>
        <td></td>
    </tr>
  <tr>
     <td style="text-align: left;">
        Podpisy członków zespołu spisowego
    </td>
    <td style="text-align: right;">
        Podpis osoby odpowiedzialnej materialnie
    </td>
  </tr>
</table>




<div class="footer"></div>

<script type="text/php">
if (isset($pdf)) {
    $font = $fontMetrics->getFont("DejaVuSans", "normal"); 
    $size = 8; 

    $text = "00 / 00";
    $pageWidth = $pdf->get_width();
    $textWidth = $fontMetrics->getTextWidth($text, $font, $size);
    $x = ($pageWidth - $textWidth) / 2;
    $y = $pdf->get_height() - 15;

    $pdf->page_text($x, $y, "{PAGE_NUM} / {PAGE_COUNT}", $font, $size, array(0,0,0));
}
</script>

</body>
</html>

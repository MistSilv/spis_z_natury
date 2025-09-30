<!DOCTYPE html>

<!-- SpisPdfController tylko -->
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Spis z natury - {{ $spis->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; }

        h1 { text-align: center; font-size: 16px; margin: 5px 0; }

        table { border-collapse: collapse; width: 100%; page-break-inside: auto; }
        th, td { border: 1px solid #000; padding: 4px 8px; font_weight: bold; }
        th { background: #9f9f9fff; }
        tfoot td { font-weight: bold; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        tbody tr:nth-child(even) {
            background-color: #f5f5f5; /* light gray for even rows */
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 10px;
        }

        /* Page counters for Dompdf */
        @page {
            margin-bottom: 40px; /* leave space for footer */
        }

        .pagenum:before { content: counter(page); }
        .totalpages:before { content: counter(pages); }

        /* Add top margin for table on subsequent pages */
        .table-wrapper {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- Preamble only for the first page -->
<div class="preamble">
    <p>Jarosławdis Sp. z o.o<br>
       ul. Wybrzeże Kościuszkowskie 45/79<br>
       00-347 Warszawa<br>
       NIP 5252675059</p>

    <p>Arkusz spisu z natury nr {{ $spis->number ?? '22/2025/SUR' }}<br>
       Nazwa lub numer pola spisowego: {{ $spis->field_name ?? 'Garmaż surowce' }}<br>
       Przedmiot spisu: {{ $spis->subject ?? 'Garmaż' }}</p>

    <p>Osoba odpowiedzialna materialnie: ____________________________</p>
    <p>Skład zespołu spisowego:<br>
       ____________________________<br>
       ____________________________<br>
       ____________________________</p>

    <p>Inne osoby obecne przy spisie:<br>
       ____________________________<br>
       ____________________________</p>

    <p>Spis z natury na dzień: {{ $date }}</p>
</div>

<div class="table-wrapper">
    <table style="font-size:10px; border-collapse: collapse; table-layout: auto; width: 100%;">


        <thead>
            <tr>
                <th style="white-space: nowrap;">Lp.</th>
                <th>Towar</th> <!-- expands -->
                <th style="white-space: nowrap;">EAN</th>
                <th style="white-space: nowrap;">Jm</th>
                <th style="text-align:right; white-space: nowrap;">Cena jedn.</th>
                <th style="text-align:right; white-space: nowrap;">Ilość</th>
                <th style="text-align:right; white-space: nowrap;">Wartość</th>

            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->ean }}</td>
                    <td>{{ $product->unit }}</td>
                    <td style="text-align:right">{{ number_format($product->unit_price, 2) }}</td>
                    <td style="text-align:right">{{ number_format($product->quantity, 2) }}</td>
                    <td style="text-align:right">{{ number_format($product->total_value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right">Razem:</td>
                <td style="text-align:right">{{ number_format($products->sum('total_value'), 2) }} zł</td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Footer with centered page numbers -->
<div class="footer"></div>

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont("DejaVuSans", "normal");
        $size = 10;

        // Only calculate width for a placeholder string wide enough
        $text = "Strona 00 / 00"; // ensures consistent width
        $pageWidth = $pdf->get_width();
        $textWidth = $fontMetrics->getTextWidth($text, $font, $size);
        $x = ($pageWidth - $textWidth) / 2;

        $y = $pdf->get_height() - 15;

        // Output dynamic text centered
        $pdf->page_text($x, $y, "Strona {PAGE_NUM} / {PAGE_COUNT}", $font, $size, array(0,0,0));
    }
</script>



</body>
</html>

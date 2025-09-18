document.addEventListener('DOMContentLoaded', function () {
    const scanner = new Html5Qrcode("reader");
    let isScanning = false;

    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');

    const tableBody = document.querySelector('#products-table tbody');

    // Add product row function (same as before)

    // Handle scanning result
    async function onScanSuccess(decodedText) {
        if (window.isProcessingScan) return;
        window.isProcessingScan = true;

        document.getElementById('scan-result').innerText = `Scanned: ${decodedText}`;

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const res = await fetch('/api/Barcode_check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ barcode: decodedText })
            });

            const data = await res.json();
            if (!res.ok || !data.product) {
                alert(data.message || "Produkt nie znaleziony");
                window.isProcessingScan = false;
                return;
            }

            const product = data.product;

            // użytkownik wpisuje ilość
            const qty = prompt(`Podaj ilość dla produktu: ${product.name}`, "1");

            if (qty && !isNaN(qty) && parseFloat(qty) > 0) {
                // zapisz do bazy
                const saveRes = await fetch('/api/scan/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        product_id: product.id,
                        quantity: parseInt(qty),
                        barcode: product.barcode
                    })
                });

                const saveData = await saveRes.json();

                if (!saveRes.ok) {
                    alert(saveData.message || "Błąd zapisu skanu.");
                } else {
                    // dodaj do tabeli na froncie
                    alert(`Zeskanowano: ${product.name}, Ilość: ${qty}`);
                }
            } else {
                alert("Nieprawidłowa ilość.");
            }

        } catch (err) {
            alert(err.message || "Błąd przy sprawdzaniu kodu.");
        } finally {
            window.isProcessingScan = false;
        }
    }


    // Start scanning
    startBtn.addEventListener('click', () => {
        if (isScanning) return;

        Html5Qrcode.getCameras().then(devices => {
            if (!devices.length) {
                alert("No cameras found.");
                return;
            }

            document.getElementById('reader').style.display = 'block';
            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');

            scanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                onScanSuccess
            ).then(() => {
                isScanning = true;
            }).catch(err => alert("Error starting scanner: " + err));

        }).catch(err => alert("Error getting cameras: " + err));
    });

    // Stop scanning
    stopBtn.addEventListener('click', () => {
        if (!isScanning) return;

        scanner.stop().then(() => {
            isScanning = false;
            document.getElementById('reader').style.display = 'none';
            startBtn.classList.remove('hidden');
            stopBtn.classList.add('hidden');
            document.getElementById('scan-result').innerText = "Scanning stopped.";
        }).catch(err => alert("Error stopping scanner: " + err));
    });
});

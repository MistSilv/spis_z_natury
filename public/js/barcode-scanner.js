document.addEventListener('DOMContentLoaded', function () {
    const scanner = new Html5Qrcode("reader");
    let isScanning = false;

    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');

    const tableBody = document.querySelector('#products-table tbody');

    // Add product row function (same as before)

    // Handle scanning result
    async function onScanSuccess(decodedText) {
        if (window.isProcessingScan) return; // blokada przed wielokrotnym wejściem
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
                window.isProcessingScan = false; // odblokuj skaner
                return;
            }

            const product = data.product;
            const qty = prompt(`Enter quantity for product: ${product.name}`, "1");

            if (qty && !isNaN(qty) && parseFloat(qty) > 0) {
                addProductRow(
                    product.id.toString(),
                    product.name,
                    parseFloat(qty),
                    product.unit || '',
                    product.price || 0
                );
            } else {
                alert("Invalid quantity.");
            }

        } catch (err) {
            alert(err.message || "Error checking barcode.");
        } finally {
            // zawsze odblokuj callback po zakończeniu prompt / alert
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

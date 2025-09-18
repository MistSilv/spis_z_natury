document.addEventListener('DOMContentLoaded', function () {
    const scanner = new Html5Qrcode("reader");
    let isScanning = false;

    const startBtn = document.getElementById('start-scan');
    const stopBtn = document.getElementById('stop-scan');

    async function onScanSuccess(decodedText) {
        if (window.isProcessingScan) return;
        window.isProcessingScan = true;

        document.getElementById('scan-result').innerText = `Scanned: ${decodedText}`;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const res = await fetch('/Barcode_check', {
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
            const qty = prompt(`Podaj ilość dla produktu: ${product.name}`, "1");

            if (qty && !isNaN(qty) && parseFloat(qty) > 0) {
                const saveRes = await fetch('/scan/save', {
                    method: 'POST',
                    credentials: 'same-origin', 
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
                    alert(`Zeskanowano: ${product.name}, Ilość: ${qty}`);
                    location.reload();
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

    startBtn.addEventListener('click', () => {
        if (isScanning) return;

        Html5Qrcode.getCameras().then(devices => {
            if (!devices.length) return alert("No cameras found.");

            document.getElementById('reader').style.display = 'block';
            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');

            scanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                onScanSuccess
            ).then(() => { isScanning = true; })
             .catch(err => alert("Error starting scanner: " + err));

        }).catch(err => alert("Error getting cameras: " + err));
    });

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

    // Funkcja edycji ilości musi być dostępna globalnie
    window.editQuantity = async function(scanId, productName, currentQty) {
        const newQty = prompt(`Podaj nową ilość dla produktu: ${productName}`, currentQty);
        if (newQty === null) return;
        if (isNaN(newQty) || parseInt(newQty) < 1) {
            alert("Nieprawidłowa ilość.");
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const res = await fetch(`/produkt-skany/${scanId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ quantity: parseInt(newQty) })
            });

            const data = await res.json();

            if (!res.ok) {
                alert(data.message || "Błąd przy aktualizacji ilości.");
            } else {
                alert(`Ilość produktu ${productName} zaktualizowana do ${newQty}`);
                location.reload();
            }
        } catch (err) {
            alert(err.message || "Błąd przy aktualizacji ilości.");
        }
    };
}); // <- tu zamykamy event listener DOMContentLoaded

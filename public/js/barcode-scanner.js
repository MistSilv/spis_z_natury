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
            // Sprawdzenie kodu kreskowego
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
                        user_id: window.loggedInUserId,
                        region_id: window.currentRegionId,
                        quantity: parseFloat(qty),
                        barcode: product.barcode
                    })
                });

                const saveData = await saveRes.json();

                if (!saveRes.ok) {
                    alert(saveData.message || "Błąd zapisu skanu.");
                } else {
                    // Dynamiczne dodanie wiersza do tabeli
                    const tbody = document.getElementById('scans-table-body');
                    const skan = saveData.scan;

                    const row = document.createElement('tr');
                    row.className = 'even:bg-black hover:bg-neutral-800/70 transition';
                    const scannedDate = new Date(skan.scanned_at);
                    const formattedDate = scannedDate.getFullYear() + '-' +
                        String(scannedDate.getMonth() + 1).padStart(2, '0') + '-' +
                        String(scannedDate.getDate()).padStart(2, '0') + ' ' +
                        String(scannedDate.getHours()).padStart(2, '0') + ':' +
                        String(scannedDate.getMinutes()).padStart(2, '0');

                    row.innerHTML = `
                        <td class="p-4">${skan.id}</td>
                        <td class="p-4">${product.name}</td>
                        <td class="p-4">${parseFloat(skan.quantity).toFixed(2)}</td>
                        <td class="p-4">${skan.barcode ?? '-'}</td>
                        <td class="p-4">${formattedDate}</td>
                        <td class="p-4 flex gap-2">
                            <button onclick="editQuantity(${skan.id}, '${product.name}', ${skan.quantity})" class="bg-sky-800 hover:bg-sky-600 text-slate-100 px-3 py-1 rounded shadow transition">Edytuj</button>
                            <form method="POST" action="/produkt-skany/${skan.id}" onsubmit="return confirm('Na pewno usunąć?');">
                                <input type="hidden" name="_token" value="${token}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="bg-red-800 hover:bg-red-600 text-slate-100 px-3 py-1 rounded shadow transition">Usuń</button>
                            </form>
                        </td>
                    `;
                    tbody.prepend(row); // dodaje wiersz na górę
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

    // Funkcja edycji ilości dostępna globalnie
    window.editQuantity = async function(scanId, productName, currentQty) {
        const newQty = prompt(`Podaj nową ilość dla produktu: ${productName}`, currentQty);
        if (newQty === null) return;
        if (isNaN(newQty) || parseFloat(newQty) < 1) {
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
                body: JSON.stringify({ quantity: parseFloat(newQty) })
            });

            const data = await res.json();

            if (!res.ok) {
                alert(data.message || "Błąd przy aktualizacji ilości.");
            } else {
                // Aktualizacja wiersza w tabeli
                const tbody = document.getElementById('scans-table-body');
                const row = Array.from(tbody.children).find(tr => parseInt(tr.children[0].innerText) === scanId);
                if (row) {
                    row.children[2].innerText = parseFloat(newQty).toFixed(2);
                }

                alert(`Ilość produktu ${productName} zaktualizowana do ${newQty}`);
            }
        } catch (err) {
            alert(err.message || "Błąd przy aktualizacji ilości.");
        }
    };
});

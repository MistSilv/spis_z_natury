// public/js/daterangepicker-init.js  (zamień istniejący)
$(function() {
    var el = document.getElementById('daterange');

    // BEZ JSON.parse — to była przyczyna błędu, dataset już daje string lub undefined
    var serverFrom = el && el.dataset.serverFrom ? el.dataset.serverFrom : null;
    var serverTo   = el && el.dataset.serverTo   ? el.dataset.serverTo   : null;

    var start = (serverFrom ? moment(serverFrom, 'YYYY-MM-DD') : moment().startOf('month'));
    var end   = (serverTo   ? moment(serverTo, 'YYYY-MM-DD')   : moment());

    function setHiddenFields(startMoment, endMoment) {
        var fromVal = startMoment ? startMoment.format('YYYY-MM-DD') : '';
        var toVal   = endMoment   ? endMoment.format('YYYY-MM-DD')   : '';
        $('.date-from').val(fromVal);
        $('.date-to').val(toVal);
    }

    function cbDisplay(startMoment, endMoment) {
        // Widoczny format: MM/DD/YYYY - MM/DD/YYYY
        $('#daterange').val(startMoment.format('MM/DD/YYYY') + ' - ' + endMoment.format('MM/DD/YYYY'));
        setHiddenFields(startMoment, endMoment);
    }

    // Inicjalizacja pickera
    $('#daterange').daterangepicker({
        startDate: start,
        endDate: end,
        autoUpdateInput: true,        // <- ważne: pozwala nadpisać istniejący zakres
        linkedCalendars: true,
        showCustomRangeLabel: true,
        alwaysShowCalendars: true,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'MM/DD/YYYY',
            cancelLabel: 'Cancel',
            applyLabel: 'Apply'
        },
        opens: 'right',
        drops: 'down'
    }, cbDisplay);

    // Jeśli mamy wartości z serwera — ustaw widoczne pole i hiddeny
    if (serverFrom && serverTo) {
        cbDisplay(moment(serverFrom,'YYYY-MM-DD'), moment(serverTo,'YYYY-MM-DD'));
    } else {
        // bez wartości — pozostaw puste (użytkownik może wybrać od razu)
        $('#daterange').val('');
        setHiddenFields(null, null);
    }

    // Apply -> aktualizuj hiddeny i input
    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        cbDisplay(picker.startDate, picker.endDate);
    });

    // Cancel -> wyczyść
    $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
        $('#daterange').val('');
        setHiddenFields(null, null);
    });

    // Gdy otwierasz picker drugi raz — ustaw wewnętrzne start/end na aktualne wartości (jeśli istnieją)
    $('#daterange').on('show.daterangepicker', function(ev, picker) {
        var currentFrom = $('.date-from').val();
        var currentTo   = $('.date-to').val();

        if (currentFrom && currentTo) {
            // ustaw picker na aktualnie wybrane daty (możesz je edytować od razu)
            picker.setStartDate(moment(currentFrom, 'YYYY-MM-DD'));
            picker.setEndDate(moment(currentTo, 'YYYY-MM-DD'));
        } else {
            // opcjonalnie: ustaw domyślnie na bieżący miesiąc
            picker.setStartDate(start);
            picker.setEndDate(end);
        }
    });
});

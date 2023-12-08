document.addEventListener('DOMContentLoaded', () => {

    const locale = document.querySelector('#_locale') ? document.querySelector('#_locale').value : 'en';

    // Make the function global so it can get called from elsewhere in the code.
    // In case of dynamic date fields for instance.
    window.initDatepickers = function() {
        let elems = document.querySelectorAll('.date');
        for (let i = 0; i < elems.length; i++) {
            // First make sure the host input has not already been initialised.
            if (elems[i].datepicker === undefined) {
                elems[i].datepicker = new C_Datepicker.init(elems[i], {
                    'autoHide': true,
                    'format': 'ddd D MMM YYYY',
                    //'timePicker': true,
                    'showDropdowns': true,
                    'timePicker24Hour': true,
                    'displayStartingDate': true,
                    'today': true,
                    'clear': true,
                    'cancel': true,
                    'locale': locale,
                }, afterInit);
            }
        }
    };

    initDatepickers();

    function afterInit(datepicker) {
        // Get the datepicker host input.
        const host = document.getElementById(datepicker.getHostAttributes('id'));
        // First check for the possible default format coming from the CMS settings or use the datepicker default format.
        const defaultFormat = (document.getElementById('_dateFormat')) ? phpToDayjs(document.getElementById('_dateFormat').value) : datepicker.getParams('format');
        // Then check if a specific date format is available through a format dataset attribute or set it to the default format.
        const format = host.hasAttribute('data-format') ? phpToDayjs(host.getAttribute('data-format')) : defaultFormat;

        // Check whether a time picker is needed (ie: whether a "time" dataset is defined).
        const timePicker = host.hasAttribute('data-time') ? true : false;
        // Set both the locale date format and the timePicker option of this picker.
        datepicker.setParams({'format': format, 'timePicker': timePicker});
        datepicker.render();

        // The starting date is used with the MySQL format.
        const mysqlFormat = timePicker ? 'YYYY-MM-DD HH:mm' : 'YYYY-MM-DD';
        // By defaut set the start date to the current date and display it.
        datepicker.startingDate = dayjs().format(mysqlFormat);
        let displayStartingDate = true;

        // A datetime has been previously set.
        if (host.getAttribute('data-date') != 0) {
            // Check for time value.
            const time = timePicker ? ' ' + host.getAttribute('data-time') : '';
            // Concatenate the date and the possible time dataset parameters.
            const date = host.getAttribute('data-date') + time;
            datepicker.startingDate = dayjs(date).format(mysqlFormat);

            if (document.getElementById('_' + datepicker.getHostAttributes('id'))) {
                // Set the hidden field to the date (and time) previously set.
                document.getElementById('_' + datepicker.getHostAttributes('id')).value = date;
            }
        }

        // Check if the host input should be empty in the first place.
        if (host.hasAttribute('data-options') && host.getAttribute('data-options').includes('startEmpty') && host.getAttribute('data-date') == 0) {
            displayStartingDate = false;
        }

        datepicker.setParams({'displayStartingDate': displayStartingDate});
    }

    document.addEventListener('afterSetDate', function(evt) {
        const date = evt.detail.date;
        let time = evt.detail.datepicker.getParams('timePicker') ? evt.detail.time : null;
        // Check for zerofill. 
        time = time && +time.length == 4 ? '0' + time : time;

        // Set the hidden field to the selected datetime
        const hostId = evt.detail.datepicker.getHostAttributes('id');
        document.getElementById('_' + hostId).value = time ? date + ' ' + time : date;

        // Set the data attributes.
        const host = document.getElementById(hostId);
        host.setAttribute('data-date', date);

        // Check first whether a time picker is used.
        if (host.getAttribute('data-time') !== undefined) {
            host.setAttribute('data-time', time);
        }
    }, false);


    function phpToDayjs(str) {
      let replacements = {
          'y' : 'YY',
          'o' : 'YYYY',
          'Y' : 'YYYY',
          'n' : 'M',
          'm' : 'MM',
          'M' : 'MMM',
          'F' : 'MMMM',
          'j' : 'D',
          'd' : 'DD',
          'w' : 'd',
          'D' : 'ddd',
          'l' : 'dddd',
          'G' : 'H',
          'H' : 'HH',
          'g' : 'h',
          'h' : 'hh',
          'i' : 'mm',
          's' : 'ss',
          'u' : 'SSS',
          'a' : 'a',
          'A' : 'A',
          // no equivalent
          'N' : '',
          'S' : '',
          'w' : '',
          'z' : '',
          'W' : '',
          't' : '', 
          'L' : '',
          'B' : '',
          'e' : '',
          'I' : '',
          'O' : '',
          'P' : '',
          'T' : '',
          'Z' : '',
          'c' : '',
          'r' : '',
          'U' : ''
      };

      return str.split('').map(chr => chr in replacements ? replacements[chr] : chr).join('');
  }

});

// Anonymous function with namespace.
const C_Datepicker = (function() {
    // The private key that gives access to the storage for private properties.
    const _key = {};

    const _private = function() { 
        // The storage object for private properties.
        const privateProperties = {};

        return function(key) {
            // Compare the given key against the actual private key. 
            if (key === _key) {
                return privateProperties;
            } 

            // If the user of the class tries to access private
            // properties, they won't have the access to the `key`
            console.error('Cannot access private properties');
            return undefined;
        };
    };

    // Private functions.

    function _initProperties(_, element) {
        _(_key).rows = 6;
        _(_key).columns = 7;
        _(_key).params = {};
        _(_key).minutes = 60;
        _(_key).hours = 24;
        _(_key).months = [];
        _(_key).years = [];
        _(_key).today = dayjs().format('YYYY-M-D');

        // Get the host element main attributes. 
        const host = {'name': null, 'id': null, 'classes': null};
        host.name = element.getAttribute('name');
        host.id = element.getAttribute('id');
        host.classes = element.classList.value;
        _(_key).host = host;
        // The div element that contains the datepicker.
        _(_key).datepicker;
        _(_key).selectedDay = null;
        _(_key).selectedTime = null;
        // The year to use in the datepicker (useful in case of leap-years).
        _(_key).dpYear = dayjs().format('YYYY');
        // The month to use in the datepicker and that contains the days to display in the grid.
        _(_key).dpMonth = dayjs().format('M');
        // Custom events triggered before or after some functions.
        _(_key).beforeSetDateEvent;
        _(_key).afterSetDateEvent;
        _(_key).beforeClearEvent;
        _(_key).afterClearEvent;
        _(_key).beforeTodayEvent;
        _(_key).afterTodayEvent;
    }

    /*
     * Initializes the datepicker with the given parameters.
     * Sets it to a default value when no parameter is given.
     */
    function _initParams(_, params) {
        _(_key).params.locale = params.locale === undefined ? 'en' : params.locale;
        _(_key).params.autoHide = params.autoHide === undefined ? false : params.autoHide;
        _(_key).params.timePicker = params.timePicker === undefined ? false : params.timePicker;
        // Set the datepicker default format.
        let format = _(_key).params.timePicker ? 'YYYY-MM-DD HH:mm' : 'YYYY-MM-DD';
        _(_key).params.format = params.format === undefined ? format : params.format;
        _(_key).params.showDropdowns = params.showDropdowns === undefined ? false : params.showDropdowns;
        _(_key).params.timePicker24Hour = params.timePicker24Hour === undefined ? false : params.timePicker24Hour;
        _(_key).params.minYear = params.minYear === undefined ? 100 : params.minYear;
        _(_key).params.maxYear = params.maxYear === undefined ? 100 : params.maxYear;
        _(_key).params.minDate = params.minDate === undefined ? null : params.minDate;
        _(_key).params.maxDate = params.maxDate === undefined ? null : params.maxDate;
        _(_key).params.daysOfWeekDisabled = params.daysOfWeekDisabled === undefined ? null : params.daysOfWeekDisabled;
        _(_key).params.datesDisabled = params.datesDisabled === undefined ? null : params.datesDisabled;
        _(_key).params.displayStartingDate = params.displayStartingDate === undefined ? false : params.displayStartingDate;
        _(_key).params.today = params.today === undefined ? false : params.today;
        _(_key).params.clear = params.clear === undefined ? false : params.clear;
        _(_key).params.cancel = params.cancel === undefined ? false : params.cancel;
    }

    /*
     * Returns the host input element.
     */
    function _getHostElement(_) {
        // Try first to get the host element by its id.
        if (_(_key).host.id) {
            return document.getElementById(_(_key).host.id);
        }

        // Next, try by its name.
        if (_(_key).host.name) {
            return document.getElementsByName(_(_key).host.name)[0];
        }

        // The host element can't be find.
        return null;
    }

    /*
     * Sets the month names.
     */
    function _setMonths(_) {
        if (_(_key).months.length === 0) {
            //
            for (let i = 0; i < 12; i++) {
                let month = i + 1;
                // Use a year in the past and set a date to the first day of each month to get the month name.
                _(_key).months[i] = dayjs('2001-' + month + '-1').format('MMMM');
            }
        }
    }

    /*
     * Sets the year range to use in the datepicker.
     */
    function _setYears(_) {
        // Check for the min and max year parameters.
        let minYear = dayjs().subtract(_(_key).params.minYear, 'year').format('YYYY');
        const maxYear = dayjs().add(_(_key).params.maxYear, 'year').format('YYYY');

        while (minYear <= maxYear) {
            _(_key).years.push(minYear++);
        }
    }

    function _setToNextMonth(_) {
        _(_key).dpMonth = Number(_(_key).dpMonth) + 1;

        // Check for the next year.
        if (_(_key).dpMonth > 12) {
            _(_key).dpMonth = 1;
            _(_key).dpYear = Number(_(_key).dpYear) + 1;
        }
    }

    function _setToPrevMonth(_) {
        _(_key).dpMonth = Number(_(_key).dpMonth) - 1;

        // Check for the previous year.
        if (_(_key).dpMonth < 1) {
            _(_key).dpMonth = 12;
            _(_key).dpYear = Number(_(_key).dpYear) - 1;
        }
    }

    /*
     * Gets the days of the week used in the datepicker grid.
     */
    function _getDaysOfWeek() {
        return dayjs.weekdaysShort();
    }

    /*
     * Sets the month selected through the month drop down list.
     */
    function _changeMonth(_) {
        const selectedMonth = parseInt(_(_key).datepicker.querySelector('.months').value) + 1;
        // Update the month to display with the newly selected month.
        _(_key).dpMonth = selectedMonth;
    }

    /*
     * Sets the year selected through the year drop down list.
     */
    function _changeYear(_) {
        const selectedYear = _(_key).datepicker.querySelector('.years').value;
        // Update the month to display with the newly selected year.
        _(_key).dpYear = selectedYear;
    }

    /*
     * Computes the days contained in the datepicker grid for a given month.
     */
    function _getDays(_) {
        const days = [];
        // Get the number of days in the month to display.
        const nbDays = dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).daysInMonth();

        // Figure out what is the first day of the month to display.
        // Returns the day as a number ie: 0 => sunday, 1 => monday ... 6 => saturday.
        const firstDayOfTheMonth = dayjs(_(_key).dpYear + '-' + _(_key).dpMonth + '-1').day();

        // Generate date for each day in the grid.
        let datepicker;
        let day;

        // The last days of the previous month have to be displayed.
        if (firstDayOfTheMonth > 0) {

            // Set the datepicker back a month.
            datepicker = dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).subtract(1, 'month').format('YYYY-M').split('-');
            // Get the numer of days contained in the previous month.
            const daysInPreviousMonth = dayjs(datepicker[0] + '-' + datepicker[1]).daysInMonth();

            // Compute the number of previous month last days to display.
            let nbLastDays = 0;
            while (nbLastDays < firstDayOfTheMonth) {
                nbLastDays++;
            }

            // Loop through the days of the previous month.
            for (let i = 0; i < daysInPreviousMonth; i++) {
                day = i + 1;

                if (day > (daysInPreviousMonth - nbLastDays)) {
                    days.push(_getDayObject(_, datepicker[0] + '-' + datepicker[1] + '-' + day, 'previous'));
                }
            }
        }

        // Loop through the days of the current month.
        for (let i = 0; i < nbDays; i++) {
            day = i + 1;
            days.push(_getDayObject(_, _(_key).dpYear + '-' + _(_key).dpMonth + '-' + day, 'current'));
        }

        // Compute the number of days needed to fill the datepicker grid.
        const nbDaysInNextMonth = (_(_key).rows * _(_key).columns) - days.length;
        // Set the datepicker forward a month.
        datepicker = dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).add(1, 'month').format('YYYY-M').split('-');

        // Loop through the days of the next month.
        for (let i = 0; i < nbDaysInNextMonth; i++) {
            day = i + 1;
            days.push(_getDayObject(_, datepicker[0] + '-' + datepicker[1] + '-' + day, 'next'));

            // The datepicker grid is filled.
            if (i > nbDaysInNextMonth) {
                break;
            }
        }

        return days;
    }

    /*
     * Build a day object to use in the datepicker grid.
     */
    function _getDayObject(_, date, position) {
        // Get the day from the given date.
        let day = date.split('-')[2];

        let today = (date === _(_key).today) ? true : false;
        let selected = (date === _(_key).selectedDay) ? true : false;
        // Check for the possible min and max dates and set the disabled attribute accordingly.
        let disabled = (_(_key).params.minDate && dayjs(date).isBefore(_(_key).params.minDate)) || (_(_key).params.maxDate && dayjs(_(_key).params.maxDate).isBefore(date)) ? true : false;
        // Check again for the disabled days of the week (if any).
        disabled = _(_key).params.daysOfWeekDisabled && _(_key).params.daysOfWeekDisabled.includes(dayjs(date).day()) ? true : disabled;
        // Check again for the disabled dates (if any).
        disabled = _(_key).params.datesDisabled && _(_key).params.datesDisabled.includes(dayjs(date).format('YYYY-MM-DD')) ? true : disabled;

        return {'text': day, 'timestamp': dayjs(date).valueOf(), 'month': position, 'today': today, 'selected': selected, 'disabled': disabled};
    }

    /*
     * Sets the datepicker year and month values according to the min and max date parameters.
     */
    function _setDates(_) {
        if (_(_key).params.minDate && dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).isBefore(_params.minDate)) {
            let minDate = dayjs(_(_key).params.minDate).format('YYYY-M').split('-');
            _(_key).dpYear = minDate[0];
            _(_key).dpMonth = minDate[1];
        }

        if (_(_key).params.maxDate && dayjs(_(_key).params.maxDate).isBefore(_(_key).dpYear + '-' + _(_key).dpMonth)) {
            let maxDate = dayjs(_(_key).params.maxDate).format('YYYY-M').split('-');
            _(_key).dpYear = maxDate[0];
            _(_key).dpMonth = maxDate[1];
        }
    }

    /*
     * Sets the host input value to the newly selected date.
     */
    function _setDate(_, timestamp) {
        // Fire the beforeSetDate event with the old selected date (if any).
        let date = _(_key).selectedDay ? dayjs(_(_key).selectedDay).format('YYYY-MM-DD') : null;
        _(_key).beforeSetDateEvent.detail.date = date;
        let time = date ? _(_key).selectedTime : null;
        _(_key).beforeSetDateEvent.detail.time = time;
        document.dispatchEvent(_(_key).beforeSetDateEvent);

        // Make sure the given timestamp is of the type number. (Note: add a plus sign to convert into number).
        timestamp = typeof timestamp != 'number' ? +timestamp : timestamp;
        // Get the selected date from the given timestamp.
        date = dayjs(timestamp).format('YYYY-MM-DD');
        // Add the time if needed.
        date = _(_key).params.timePicker ? date + ' ' + _getTime(_) : date;
        _getHostElement(_).value = dayjs(date).format(_(_key).params.format);

        // Unselect the old selected day in the datepicker grid.
        let oldDay = _(_key).datepicker.querySelector('.datepicker-grid .selected');

        if (oldDay) {
           oldDay.classList.remove('selected');
        }

        // Add the class to the newly selected day.
        let newDay = _(_key).datepicker.querySelector('[data-date="' + timestamp + '"]');

        if (newDay){
            newDay.classList.add('selected');
        } 

        // Update the selected day attribute.
        _(_key).selectedDay = dayjs(date).format('YYYY-M-D');

        // As well as the selected time attribute (if timePicker is active).
        if (_(_key).params.timePicker) {
            _(_key).selectedTime = _getTime(_);
        }

        // Fire the afterSetDate event with the newly selected date.
        _(_key).afterSetDateEvent.detail.date = dayjs(date).format('YYYY-MM-DD');
        time = _(_key).params.timePicker ? _getTime(_) : null;
        _(_key).afterSetDateEvent.detail.time = time;
        document.dispatchEvent(_(_key).afterSetDateEvent);
    }

    function _clearDate(_) {
        // Fire the beforeClear event with the old selected date.
        let date = _(_key).selectedDay ? dayjs(_(_key).selectedDay).format('YYYY-MM-DD') : null;
        _(_key).beforeClearEvent.detail.date = date;
        let time = date ? _(_key).selectedTime : null;
        _(_key).beforeClearEvent.detail.time = time;
        document.dispatchEvent(_(_key).beforeClearEvent);

        _getHostElement(_).value = '';
        _(_key).selectedDay = null;
        _updateDatepicker(_);

        document.dispatchEvent(_(_key).afterClearEvent);
    }

    function _setToday(_) {
        // Fire the beforeToday event with the old selected date (if any).
        let date = _(_key).selectedDay ? dayjs(_(_key).selectedDay).format('YYYY-MM-DD') : null;
        _(_key).beforeTodayEvent.detail.date = date;
        let time = date ? _(_key).selectedTime : null;
        _(_key).beforeTodayEvent.detail.time = time;
        document.dispatchEvent(_(_key).beforeTodayEvent);

        const today = dayjs().format("YYYY-MM-DD");
        const timestamp = dayjs(today).valueOf();
        _setDate(_, timestamp);

        document.dispatchEvent(_(_key).afterTodayEvent);
    }

    /*
     * Returns the selected time into the HH:mm format.
     */
    function _getTime(_) {
        // Make sure the drop down lists of time exist.
        if (_(_key).params.timePicker) {
            let hour = _(_key).datepicker.querySelector('[name="hours"]').value;
            let minute = _(_key).datepicker.querySelector('[name="minutes"]').value;
            const TwelveHourClock = _(_key).params.timePicker24Hour ? false : true;

            // Check for meridiem format (am / pm)
            if (TwelveHourClock) {
                // Convert hour into 24 hour format.
                if (_(_key).datepicker.querySelector('[name="meridiems"]').value == 'pm') {
                    hour = hour < 12 ? +hour + 12 : 12;
                }
                // am
                else {
                    // 12 am is midnight in 12 hour clock. Thus zero in 24 hour clock. 
                    hour = hour < 12 ? hour : 0;

                }
            }

            // Check for zerofill.
            hour = hour < 10 ? '0' + hour : hour;
            minute = minute < 10 ? '0' + minute : minute;

            return hour + ':' + minute;
        }

        return null;
    }

    /*
     * Builds and returns the datepicker.
     */
    function _renderDatepicker(_) {
        let html = `<div class="datepicker datepicker-dropdown datepicker-orient-left datepicker-orient-bottom">`+
                   `<div class="datepicker-picker">`+`<div class="datepicker-header">`+`<div class="datepicker-title" style="display: none;"></div>`+
                   `<div class="datepicker-controls">`;

        // Check for the min date and disable the previous button accordingly.
        let disabled = (_(_key).params.minDate && dayjs(dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).subtract(1, 'month').format('YYYY-M')).isBefore(_(_key).params.minDate)) ? 'disabled' : '';
        html += `<button type="button" class="button prev-button prev-btn" `+ disabled +` tabindex="-1">«</button>`;

        // Build both the year and month drop down lists.
        if (_(_key).params.showDropdowns) {
            html += `<div class="datepicker-dropdown-date"><select name="months" class="months">`;

            for (let i = 0; i < _(_key).months.length; i++) {
                let selected = i == _(_key).dpMonth - 1 ? 'selected' : '';
                html += `<option value="` + i + `" ` + selected + `>` + _(_key).months[i] + `</option>`;
            }

            html += `</select><select name="years" class="years">`;

            for (let i = 0; i < _(_key).years.length; i++) {
                let selected = _(_key).years[i] == _(_key).dpYear ? 'selected' : '';
                html += `<option value="` + _(_key).years[i] + `" ` + selected + `>` + _(_key).years[i] + `</option>`;
            }

            html += `</select></div>`;
        }
        else {
            html += `<button type="button" class="button view-switch" tabindex="-1">`+dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).format('MMMM YYYY')+`</button>`;
        }

        // Check for the max date and disable the next button accordingly.
        disabled = (_(_key).params.maxDate && dayjs(dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).add(1, 'month').format('YYYY-M')).isAfter(_(_key).params.maxDate)) ? 'disabled' : '';
        html += `<button type="button" class="button next-button next-btn" `+ disabled +` tabindex="-1">»</button>`+`</div></div>`+
                `<div class="datepicker-main"><div class="datepicker-view"><div class="days"><div class="days-of-week">`;

        _getDaysOfWeek().forEach((day) => {
            html += `<span class="dow">`+day+`</span>`;
        });

        html += `</div><div class="datepicker-grid">`;

        const days = _getDays(_);
        days.forEach((day) => {
            let extra = (day.month != 'current') ? day.month : '';
            extra += (day.today) ? ' today' : '';
            extra += (day.selected) ? ' selected' : '';
            extra += (day.disabled) ? ' disabled' : '';
            html += `<span data-date="`+day.timestamp+`" class="datepicker-cell day `+extra+`">`+day.text+`</span>`;
        });

        html += `</div></div></div></div>`+
                `<div class="datepicker-footer">`;

        // Build the time drop down lists.
        if (_(_key).params.timePicker) {
            // Set the time units to explode according to the timePicker24Hour parameter.
            let format = _(_key).params.timePicker24Hour ? 'H:m' : 'h:m:a';
            let time = dayjs().format(format);
            // Explode the time units into an array.
            time = time.split(':');

            html += `<div class="datepicker-time"><select name="hours" class="hours">`;

            const hours = _(_key).params.timePicker24Hour ? _(_key).hours : 13;

            for (let i = 0; i < hours; i++) {
                // No zero hour in meridiem format.
                if (i === 0 && !_(_key).params.timePicker24Hour) {
                    continue;
                }

                let selected = i == time[0] ? 'selected' : '';
                html += `<option value="`+ i +`" `+ selected +`>`+ i +`</option>`;
            }

            html += `</select><select name="minutes" class="minutes">`;

            for (let i = 0; i < _(_key).minutes; i++) {
                let selected = i == time[1] ? 'selected' : '';
                let zerofill = i < 10 ? '0' : '';
                html += `<option value="`+ i +`" `+ selected +`>`+ zerofill + i +`</option>`;
            }

            html += `</select>`;

            // Build the meridiem drop down list.
            if (!_(_key).params.timePicker24Hour) {
                html += `<select name="meridiems" class="meridiems">`;

                const meridiems = ['am', 'pm'];
                for (let i = 0; i < meridiems.length; i++) {
                    let selected = meridiems[i] == time[2] ? 'selected' : '';
                    html += `<option value="`+ meridiems[i] +`" `+ selected +`>`+ meridiems[i] +`</option>`;
                }

                html += `</select>`;
            }

            html += `</div>`;
        }

        // Build the control buttons according to the parameter setting.
        html += `<div class="datepicker-controls">`;

        if (_(_key).params.today) {
            html += `<button type="button" class="ctrl-button today" tabindex="-1" >`+ CodaliaLang.datepicker['today'] +`</button>`;
        }

        if (_(_key).params.clear) {
            html += `<button type="button" class="ctrl-button clear" tabindex="-1" >`+ CodaliaLang.datepicker['clear'] +`</button>`;
        }

        if (_(_key).params.cancel) {
            html += `<button type="button" class="ctrl-button cancel" tabindex="-1" >`+ CodaliaLang.datepicker['cancel'] +`</button>`;
        }

        html += `</div></div></div>`;

        return html;
    }

    /*
     * Updates the grid as well as some parts of the datepicker according to the recent changes.
     */
    function _updateDatepicker(_) {
        // Update the date drop down lists.
        if (_(_key).params.showDropdowns) {
            // Unselect the old selected month.
            _(_key).datepicker.querySelector('.months').selected = false;
            // Get the numeric value of the month to display (ie: 0 => January, 1 => February...).
            const monthNumeric = dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).format('M') - 1;
            // Update the selected option.
            _(_key).datepicker.querySelector('.months option[value="'+ monthNumeric +'"]').selected = true;

            // Same with year.
            _(_key).datepicker.querySelector('.years').selected = false;
            const year = dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).format('YYYY');
            _(_key).datepicker.querySelector('.years option[value="'+ year +'"]').selected = true;
        }
        // Update the text date.
        else {
            _(_key).datepicker.querySelector('.view-switch').innerHTML = dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).format('MMMM YYYY');
        }

        // Update the datepicker grid.

        const days = _getDays(_);
        let grid = '';

        days.forEach((day) => {
            let extra = (day.month != 'current') ? day.month : '';
            extra += (day.today) ? ' today' : '';
            extra += (day.selected) ? ' selected' : '';
            extra += (day.disabled) ? ' disabled' : '';
            grid += `<span data-date="` + day.timestamp + `" class="datepicker-cell day ` + extra + `">` + day.text + `</span>`;
        });

        _(_key).datepicker.querySelector('.datepicker-grid').innerHTML = grid;

        // Update both the previous and next buttons according to the month currently displayed in the datepicker.

        _(_key).datepicker.querySelector('.prev-button').disabled = false;
        if (_(_key).params.minDate) {
            // Get the year and month of the min date to compare with the datepicker's.
            let minDate = dayjs(_(_key).params.minDate).format('YYYY-M');
            if (dayjs(dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).subtract(1, 'month').format('YYYY-M')).isBefore(minDate)) {
                _(_key).datepicker.querySelector('.prev-button').disabled = true;
            }
        }

        _(_key).datepicker.querySelector('.next-button').disabled = false;
        if (_(_key).params.maxDate) {
            // Get the year and month of the min date to compare with the datepicker's.
            let maxDate = dayjs(_(_key).params.maxDate).format('YYYY-M');
            if (dayjs(dayjs(_(_key).dpYear + '-' + _(_key).dpMonth).add(1, 'month').format('YYYY-M')).isAfter(maxDate)) {
                _(_key).datepicker.querySelector('.next-button').disabled = true;
            }
        }

        // Update the time drop down lists only when it's called by the _setStartingDate function.
        if (_updateDatepicker.caller.name == '_setStartingDate' && _(_key).params.timePicker) {
            // Use a date in the past to get the time in the desired format.
            let time = dayjs('2001-01-01 ' + _(_key).selectedTime).format('H:m').split(':');
            let meridiem = 'am';

            // Convert the 24 hour time to 12 hour time.
            if (!_(_key).params.timePicker24Hour) {
                if (time[0] > 12) {
                    time[0] = +time[0] - 12;
                    meridiem = 'pm';
                }

                // Midnight
                if (time[0] == 0) {
                    time[0] = 12;
                }
                // Noon is considered as post meridiem
                else if (time[0] == 12) {
                    meridiem = 'pm';
                }
            }

            // Unselect the old selected hour.
            _(_key).datepicker.querySelector('.hours').selected = false;
            // Update the selected option.
            _(_key).datepicker.querySelector('.hours option[value="'+ time[0] +'"]').selected = true;
            // Unselect the old selected minute.
            _(_key).datepicker.querySelector('.minutes').selected = false;
            // Update the selected option.
            _(_key).datepicker.querySelector('.minutes option[value="'+ time[1] +'"]').selected = true;

            if (_(_key).datepicker.querySelector('.meridiems')) {
                _(_key).datepicker.querySelector('.meridiems').selected = false;
                _(_key).datepicker.querySelector('.meridiems option[value="'+ meridiem +'"]').selected = true;
            }
        }
    }

    /*
     * Called just one time through the callback function.
     */
    function _setStartingDate(_, date) {
        if (_(_key).params.displayStartingDate) {
            _getHostElement(_).value = dayjs(date).format(_(_key).params.format);
        }

        _(_key).selectedDay = dayjs(date).format('YYYY-M-D');
        _(_key).dpMonth = dayjs(date).format('M');
        _(_key).dpYear = dayjs(date).format('YYYY');
        _(_key).selectedTime = _(_key).params.timePicker ? dayjs(date).format('HH:mm') : null;
        _updateDatepicker(_);
    }


    // The datepicker constructor.
    const _Datepicker = function(element, params, callback) {
        // Creates a private object
        this._ = _private(); 

        // Initialize both private properties and parameters.
        _initProperties(this._, element);
        _initParams(this._, params);

        // Some DayJS functions require the locale data plugin.
        dayjs.extend(window.dayjs_plugin_localeData);
        // Set the locale for the datepicker.
        dayjs.locale(this._(_key).params.locale);

        _setYears(this._);
        _setMonths(this._);
        _setDates(this._);

        // Create a div container for the datepicker.
        this._(_key).datepicker = document.createElement('div');
        this._(_key).datepicker.classList.add('datepicker-container');
        // Insert the datepicker in the container.
        this._(_key).datepicker.insertAdjacentHTML('afterbegin', _renderDatepicker(this._));
        // Insert the div container after the given element.
        _getHostElement(this._).insertAdjacentElement('afterend', this._(_key).datepicker);

        // Hide the datepicker.
        this.hide();

        // Delegate the click event to the datepicker element to check whenever an element is clicked.
        this._(_key).datepicker.addEventListener('click', this, false);

        this.handleEvent = function(evt) {
            // Check the day (make sure it's not disabled)
            if (evt.target.classList.contains('day') && !evt.target.classList.contains('disabled')) {
                _setDate(this._, evt.target.dataset.date);

                if (this._(_key).params.autoHide) {
                    this._(_key).datepicker.style.display = 'none';
                }
            }

            // Check for button.

            if (evt.target.classList.contains('prev-button')) {
                _setToPrevMonth(this._);
                _updateDatepicker(this._);
            }

            if (evt.target.classList.contains('next-button')) {
                _setToNextMonth(this._);
                _updateDatepicker(this._);
            }

            if (evt.target.classList.contains('cancel')) {
                this._(_key).datepicker.style.display = 'none';
            }

            if (evt.target.classList.contains('clear')) {
                _clearDate(this._);

                if (this._(_key).params.autoHide) {
                    this._(_key).datepicker.style.display = 'none';
                }
            }

            if (evt.target.classList.contains('today')) {
                _setToday(this._);
                _updateDatepicker(this._);

                if (this._(_key).params.autoHide) {
                    this._(_key).datepicker.style.display = 'none';
                }
            }
        }

        // Show or hide the datepicker according to where the user clicks (outside the datepicker or inside the host input element).
        function showHide(evt) {
            // The clicked target is not the input host and is not contained into the datepicker element.
            if (evt.target !== _getHostElement(this._) && !this._(_key).datepicker.contains(evt.target)) {
                this._(_key).datepicker.style.display = 'none';
            }

            // The user has clicked into the host input element.
            if (evt.target === _getHostElement(this._)) {
                this._(_key).datepicker.style.display = 'block';
            }
        }

        document.addEventListener('click', showHide.bind(this), false);

        // Set the month and year attributes of the datepicker whenever the month and year drop down lists change.
        function setMonthYear(evt) {
            if (this._(_key).params.showDropdowns && evt.target.classList.contains('months')) {
                _changeMonth(this._);
                _updateDatepicker(this._);
            }

            if (this._(_key).params.showDropdowns && evt.target.classList.contains('years')) {
                _changeYear(this._);
                _updateDatepicker(this._);
            }
        }

        document.addEventListener('change', setMonthYear.bind(this), false);

        // Create and initialise the custom events
        this._(_key).beforeSetDateEvent = new CustomEvent('beforeSetDate', {detail: {datepicker: this, date: null, time: null}});
        this._(_key).afterSetDateEvent = new CustomEvent('afterSetDate', {detail: {datepicker: this, date: null, time: null}});
        this._(_key).beforeClearEvent = new CustomEvent('beforeClear', {detail: {datepicker: this, date: null, time: null}});
        this._(_key).afterClearEvent = new CustomEvent('afterClear', {detail: {datepicker: this}});
        this._(_key).beforeTodayEvent = new CustomEvent('beforeToday', {detail: {datepicker: this, date: null, time: null}});
        this._(_key).afterTodayEvent = new CustomEvent('afterToday', {detail: {datepicker: this}});

        // Run the given callback function.
        if (callback !== undefined) {
            callback(this);

            // Check for a possible starting date.
            if (this.startingDate !== undefined) {
                _setStartingDate(this._, this.startingDate);
            }
        }
    };

    // Public methods.

    _Datepicker.prototype = {
        today: function(format) {
            format = format !== undefined ? format : this._(_key).params.format;
            return dayjs().format(format);
        },

        current: function() {
            return dayjs(this._(_key).dpYear + '-' + this._(_key).dpMonth).format('YYYY-MM-DD');
       },

        setParams: function(params) {
            for (const key in params) {
                this._(_key).params[key] = params[key];
            }
        },

        getParams: function(name) {
            return name === undefined ? this._(_key).params : this._(_key).params[name];
        },

        // Rebuilds all the datepicker.
        render: function() {
            this._(_key).datepicker.innerHTML = _renderDatepicker(this._);
        },

        clear: function() {
            _clearDate(this._);
        },

        show: function() {
            this._(_key).datepicker.style.display = 'block';
        },

        hide: function() {
            this._(_key).datepicker.style.display = 'none';
        },

        getHostElement: function() {
            return _getHostElement(this._);
        },

        getHostAttributes: function(name) {
            return name === undefined ? this._(_key).host : this._(_key).host[name];
        },
    };

    // Returns a init property that returns the "constructor" function.
    return {
        init: _Datepicker
    }
})();


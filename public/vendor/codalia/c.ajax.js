// Anonymous function with namespace.
const C_Ajax = (function() {

    // Private methods and properties.

    let _xhr = null;
    const _params = {};

    /**
     * Turns the given query parameters into an encoded query string.
     *
     * @param   object   queryParams      The query parameters for the Ajax request.
     * @return  string                    The query string as an encoded variable string for the Ajax request.
    */
    function _buildQueryString(queryParams) {
        let queryString = '';
        // Loops through the given queryParams object.
        for (var key in queryParams) {
            // Checks for arrays.
            if (Array.isArray(queryParams[key])) {
                for (var i = 0; i < queryParams[key].length; i++) {
                    // Encodes the array values.
                    queryString += key+'='+encodeURIComponent(queryParams[key][i])+'&';
                }
            }
            else {
                // Encodes the query parameters values.
                queryString += key+'='+encodeURIComponent(queryParams[key])+'&';
            }
        }

        // Removes the & character from the end of the string.
        queryString = queryString.slice(0, -1);

        return queryString;
    }

    /**
     * The initial function that initialized the AJAX request.
     *
     * @param   object   params           The parameters for the Ajax request.
     * @param   object   queryParams      The query parameters. (optional)
     *
     * @return  void
    */
    const _Ajax = function(params, queryParams) {
        // Initializes the XMLHttpRequest object.
        _xhr = new XMLHttpRequest();

        // Checks the given parameters for the current Ajax request and modified them if needed.
        _params.method = params.method === undefined || (params.method.toUpperCase() !== 'GET' && params.method.toUpperCase() !== 'POST') ? 'GET' : params.method.toUpperCase();
        _params.url = params.url === undefined ? window.location.href : params.url;
        _params.dataType = params.dataType === undefined || (params.dataType != 'json' && params.dataType != 'xml' && params.dataType != 'text') ? 'text' : params.dataType;
        _params.async = params.async === undefined ? true : params.async;
        _params.indicateFormat = params.indicateFormat === undefined ? false : params.indicateFormat;
        _params.headers = params.headers === undefined ? {} : params.headers;
        _params.data = params.data === undefined ? null : params.data;

        // Prepares the Ajax request with the given parameters and data.

        // Sets the url and query string according to the given data.
        let url = _params.url;
        let queryString = null;

        if (queryParams !== undefined) {
            queryString = _buildQueryString(queryParams);
            // Adds the query string to the given url.
            if (_params.method == 'GET') {
                queryString = '?'+queryString;
                // Checks whether a query is already contained in the given url.
                let regex = /\?/;

                if (regex.test(_params.url)) {
                    // Adds the variables after the query already existing.
                    queryString = queryString.replace('?', '&');
                }

                url = url+queryString;
           }
        }

        // Initializes the newly-created request.
        _xhr.open(_params.method, url, _params.async);

        // Forces the MIME Type according to the given dataType.
        if (_params.dataType == 'json') {
            _xhr.overrideMimeType('application/json');
        }
        else if (_params.dataType == 'xml') {
            _xhr.overrideMimeType('text/xml');
        }
        else {
            _xhr.overrideMimeType('text/plain');
        }

        if (_params.method == 'POST') {
            // Send the headers along with the request.
            for (const [header, value] of Object.entries(_params.headers)) {
                _xhr.setRequestHeader(header, value);
            }

            _xhr.send(_params.data);
        }
        else {
            // Always null with the GET method.
            _xhr.send(null);
        }
    };

    _Ajax.prototype = {
	// Public methods.

       /**
         * Runs the EventHandler that is called whenever the readyState attribute changes. Calls the given callback
         * function when the Ajax request has succeeded.
         *
         * @param   string   callback  The name of the callback function to call when the Ajax request has succeeded.
         * @return  void/boolean       Returns false whether the Ajax request or the JSON parsing fail. Void otherwise.
        */
        run: function(callback) {
            const xhrRef = _xhr; // Storing reference.
            let params = _params;

            xhrRef.onreadystatechange = function () {
                // The Ajax request is done.
                if (xhrRef.readyState === 4) {
                    // By default returns response as plain text.
                    let response = xhrRef.responseText;

                    // Formats response according to the given dataType.
                    if (params.dataType == 'json') {
                        try {
                            response = JSON.parse(xhrRef.responseText);
                        }
                        catch (e) {
                            console.log('Parsing error: '+e);
                            return false;
                        }
                    }
                    else if (params.dataType == 'xml') {
                        response = xhrRef.responseXML;
                    }

                    if (xhrRef.status !== 200) {
                        console.log(xhrRef.status + ': ' + xhrRef.statusText);
                    }

                    // To get header information in debugging mode.
                    //console.log(xhrRef.getAllResponseHeaders());

                    if (callback !== undefined) {
                        // Calls the given callback function.
                        callback(xhrRef.status, response);
                    }
                }
            }
        }
    };

    // Returns a init property that returns the "constructor" function.
    return {
        init: _Ajax
    }

})();


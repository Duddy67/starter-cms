// Get references to input field and autocomplete container
const inputField = document.getElementById('search');
const autocompleteList = document.getElementById('autocomplete-list');
// The minimum number of characters to start searching.
const minNumCharacters = 2;

// Function to fetch autocomplete suggestions based on user input
async function fetchSuggestions(input) {
    const autocompleteUrl = document.getElementById('autocompleteUrl').value;
    const response = await fetch(autocompleteUrl + '?query=' + input);

    // Throw an error in case the response status is different from 200 (ie: OK).
    if (response.status !== 200) {
        throw new Error('Couldn\'t fetch the data. status: ' + response.status);
    }

    // Wait until the promise returned by the response object is completed.
    const suggestions = await response.json();

    return suggestions;
}

// Function to display autocomplete suggestions
function displaySuggestions(suggestions, input) {
    // Clear previous suggestions
    autocompleteList.innerHTML = '';
    // Show the suggestions list.
    document.getElementById('autocomplete-list').style.display = 'block';

    // Display new suggestions
    suggestions.forEach(suggestion => {
        const suggestionElement = document.createElement('div');
        suggestionElement.setAttribute('class', 'suggestion');
        // Highlight the user's input.
        const regex = new RegExp(`(${input})`, 'gi');
        const highlightedContent = suggestion.replace( regex, '<span class="highlight">$1</span>');
        suggestionElement.innerHTML = highlightedContent;

        suggestionElement.addEventListener('click', () => {
            // Handle selection of suggestion
            inputField.value = suggestion;
            // Clear suggestions after selection
            autocompleteList.innerHTML = ''; 
            // Hide the suggestion list.
            document.getElementById('autocomplete-list').style.display = 'none';
        });

        autocompleteList.appendChild(suggestionElement);
    });
}

// Event listener for input field to fetch and display suggestions
inputField.addEventListener('input', () => {
    const input = inputField.value;

    // Don't treat inputs starting or ending with a space character.
    if (input.startsWith(' ') || input.endsWith(' ')) {
        return;
    }

    if (input.length > minNumCharacters) {
        fetchSuggestions(input).then(suggestions => {
            displaySuggestions(suggestions, input);
        }).catch(error => {
            console.log('Error: ' + error.message);
        })
    }
    else {
        document.getElementById('autocomplete-list').style.display = 'none';
    }
});


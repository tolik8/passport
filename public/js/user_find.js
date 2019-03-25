$(document).ready(function() {

    function DevBridgeAutocompleteInit() {
        //https://www.devbridge.com/sourcery/components/jquery-autocomplete/
        $('#find').devbridgeAutocomplete({
            serviceUrl: 'user_find',
            type: 'POST',
            noCache: false,
            minChars: 3,
            onSelect: function (suggestion) {
                //console.log('You selected: ' + suggestion.value + ', ' + suggestion.data);
                $('#guid').val(suggestion.data);
            }
        });
    }

    if ($('#checkbox').prop('checked')) {
        DevBridgeAutocompleteInit();
    }

    $('#checkbox').click(function() {
        if ($('#checkbox').prop('checked')) {
            DevBridgeAutocompleteInit();
        } else {
            $('#guid').val('');
            $('#find').autocomplete('dispose');
        }
    });

});
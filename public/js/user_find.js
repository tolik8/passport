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

$('#checkbox').click(function() {
    if ($('#checkbox').prop('checked')) {
        $('#find').autocomplete().enable();
    } else {
        $('#guid').val('');
        console.log($('#find').autocomplete());
        $('#find').autocomplete('clear');
        $('#find').autocomplete('clearCache');
        $('#find').autocomplete('hide');
        // $('#find').autocomplete().clear();
        // $('#find').autocomplete().clearCache();
        // $('#find').autocomplete().hide();
        $('#find').autocomplete().disable();
        // $('#find').autocomplete().dispose();
    }
});
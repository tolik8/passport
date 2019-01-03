$(document).ready(function() {

    let i = 0;
    let max_i = 1800; /* 1800 сек = 30 хвилин */
    let j = 10;

function updateClock () {
    /*console.log('123');
    console.log(i);*/
    /*$('#counter').html(j);*/
    /*var $guid = $('#login').text();*/
    /*var $guid = findGetParameter('guid');*/
    var $url = window.location.href;
    var result = $url.match(/\/[0-9A-Z]{1,32}$/gi);
    var $guid = result[0].substr(1);
    var $domen = $url.match(/http:\/\/[0-9A-Z.]+\//gi)
    var $ajax_url = $domen + "pasport/ajax/" + $guid;
    console.log($ajax_url);

    var request = $.ajax({
        url: $ajax_url,
        method: "GET",
        cache: false,
        dataType: "html"
    });
    
    request.done(function(msg){
        $('#steps').html(msg);
    });

    request.fail(function(jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
    });
}

setTimeout(function run() {
    i++;
    j--;
    if (j < 1) {
        j = 10;
        updateClock();
    }
    if (i > max_i) return;
    setTimeout(run, 1000);
}, 1000);

});
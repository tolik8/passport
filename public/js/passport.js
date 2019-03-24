$(document).ready(function() {

    let i = 0;
    let max_i = 1800; /* 1800 сек = 30 хвилин */
    let j = 5;
    let max_j = 5;

function updateClock () {
    let $url = window.location.href;
    let result = $url.match(/\/[0-9A-Z]{1,32}$/gi);
    // let $guid = result[0].substr(1);
    let $guid = $('#guid').text();
    let $domen = $url.match(/http:\/\/[0-9A-Z.]+\//gi);
    let $ajax_url = $domen + 'passport/ajax/' + $guid;

    let request = $.ajax({
        url: $ajax_url,
        method: 'GET',
        cache: false,
        dataType: 'json'
    });
    
    request.done(function(data){
        data.forEach(function(item){
            work_id = item['WORK_ID'];
            tm = item['TM'];

            el = '#id'+work_id;
            $(el).text(tm);
        });
    });

    request.fail(function(jqXHR, textStatus) {
        //alert('Request failed: ' + textStatus);
    });
}

setTimeout(function run() {
    i++;
    j--;
    if (j < 1) {
        j = max_j;
        updateClock();
    }
    if (i > max_i) return;
    setTimeout(run, 1000);
}, 1000);

});
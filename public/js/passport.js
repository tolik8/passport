$(document).ready(function() {

    let i = 0;
    let max_i = 1800; /* 1800 сек = 30 хвилин */
    let j = 5;
    let max_j = 5;
    let finish = false;
    let debug = $('#debug').text();
    let $url = window.location.href;
    // let result = $url.match(/\/[0-9A-Z]{1,32}$/gi);
    // let $guid = result[0].substr(1);
    let $guid = $('#guid').text();
    let $domen = $url.match(/http:\/\/[0-9A-Z.]+\//gi);
    let $ajax_url = $domen + 'passport/ajax/' + $guid;

    function updateClock () {

    let request = $.ajax({
        url: $ajax_url,
        method: 'GET',
        cache: false,
        dataType: 'json'
    });
    
    request.done(function(data){
        data.forEach(function(item){
            let task_id = item['TASK_ID'];
            let tm = item['TM'];

            let el = '#id' + task_id;
            if (debug === '1' && task_id !== 0) {
                $(el).text(tm);
            } else {
                $(el).text('+');
            }

            if (task_id === '0' && tm !== null) {
                $('#loading').fadeOut();
                $('#total-time').removeClass('d-none');
                $('#excel').removeClass('d-none');
                $('#id0').text(tm);
                finish = true;
            }
        });
    });

    request.fail(function(jqXHR, textStatus) {
        console.log('Request failed: ' + textStatus);
    });
}

setTimeout(function run() {
    i++;
    j--;
    if (j < 1 && !finish) {
        j = max_j;
        updateClock();
    }
    if (i > max_i) return;
    setTimeout(run, 1000);
}, 1000);

});
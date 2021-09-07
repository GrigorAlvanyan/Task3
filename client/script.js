// const ws = new WebSocket('ws://localhost:2346');
//
//
//
//     $(window).on('load', function(data) {
//         $('#exampleModal').modal('show');
//         console.log(data);
//     });



ws = new WebSocket("ws://127.0.0.1:2346");
ws.onopen = function() {
    ws.send('');
};
ws.onmessage = function(e) {
    if (e.data) {
        $('#exampleModal').modal('show');
        console.log(e.data);
        var items = (e.data).split(',');
        items = items[0].split(': ')
        console.log(e.data)

        $('#exampleModal .modal-body #phone').attr( 'href', items[1]).html(e.data);

    }
};
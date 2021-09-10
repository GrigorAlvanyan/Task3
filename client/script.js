

ws = new WebSocket("ws://127.0.0.1:12345");
ws.onopen = function() {
    ws.send('12345');
};
ws.onmessage = function(e) {
    if (e.data) {
        $('#exampleModal').modal('show');
        console.log(e.data);
        var items = (e.data).split(',');
        items = items[0].split(': ')
        console.log(e.data)

        $('#exampleModal .modal-body #phone').attr( 'href', 'http://localhost/task_3/index.php?' + items[1]).html(e.data);

    }
};
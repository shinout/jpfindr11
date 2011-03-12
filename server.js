var http = require('http');
    spawn = require('child_process').spawn,
    geturl = spawn('php', ['scripts/geturl.php']);


http.createServer(function (req, res) {
  switch (req.url) {
    case "/":
    default :
      res.writeHead(200, {'Content-Type': 'text/html'});
      geturl.stdout.on('data', function(data) {
        var ahref = '<a href="'+data+'">click here</a>';
        res.end(ahref);
      });
      geturl.stderr.on('data', function(data) {
        console.log("stderr: "+data);
      });
      break;
  }
}).listen(8124, "49.212.2.136");

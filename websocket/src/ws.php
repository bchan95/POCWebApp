<?php
    use Ratchet\Server\IoServer;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
    use React\Socket\SecureServer as Reactor;
    use React\EventLoop\Factory as LoopFactory;
    use ws\grpcChat;
    require dirname(__DIR__) . '/vendor/autoload.php';
    $loop = LoopFactory::create();
    $socket = new React\Socket\Server(8443, $loop);
    $server = new IoServer(
        new HttpServer(
            new WsServer(
                new grpcChat()
            )
        ),
         /*
          * //will need to get a new certificate that actually works
          */
         //pass react server to ratchet to enable ssl security
        new Reactor($socket,$loop, array(
            'local_cert' =>dirname(__DIR__) . '/localhost_ssl/certificate.pem'
        )
        ),
        $loop


    );
   // echo dirname(__DIR__) . '/public.pem';


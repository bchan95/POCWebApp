<?php
//whoever designed this garbage language should be shot
namespace ws;
use Backendservice\backendServiceClient;
use Backendservice\SpeechRequest;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory as Looper;
use Grpc;
class grpcChat implements MessageComponentInterface{
    protected  $clients;
    protected $client;
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->client = new backendServiceClient('localhost:8980', [
            // TODO: implement ssl encryption here and also attach a jwt to headers
            'credentials' => Grpc\ChannelCredentials::createInsecure(),
        ]);
    }

    public function  onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach(new client($conn));
        echo("connected ");
    }
    public function onMessage(ConnectionInterface $from, $msg)
    {
        foreach ($this->clients as $wsclient){
            if($from == $wsclient->getConnection()){
                if($msg=="START"){
                    echo $msg. " ";
                    $wsclient->listen($this->client->getTest());
                }
                else if($msg=="COMPLETED"){
                    if($wsclient->getCall()!=null) {
                        echo "done";
                        $wsclient->getCall()->writesDone();
                        $wsclient->listen(null);
                    }
                }
                else {
                    $request = new SpeechRequest();
                    $request->setAudioData($msg);
                    if($wsclient->getCall() != null) {
                        $wsclient->getCall()->write($request);
                    }
                }
            }
        }
    }
    public function onClose(ConnectionInterface $conn)

    {
        foreach ($this->clients as $client){
            if($conn == $client->getConnection()){
                if($client->getCall()!=null) {
                    $client->getCall()->writesDone();
                }
            }
        }
    }
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: " . $e;
        /*foreach($this->clients as $client){
            if($client->getConnection() == $conn){
                if($client->getCall() != null){
                    $client->getCall()->writesDone();
                }
            }
        }*/
    }

}
class client{
    //class holds all information regarding connected client
    protected $connection; //websocket connection
    protected $call; //call to backend object
    public function __construct(ConnectionInterface $conn){
        $this ->connection = $conn;
    }
    public function getCall(){
        return $this->call;
    }
    public function getConnection(){
        return $this->connection;
}
    public function listen($call)
    {
        //because there is currently no asynchronous method to stream with gRPC, we must alternate between writing
        //to the stream and listening for responses to the stream. Really not an ideal solution, but it's all we can
        //really do at this point

        //look into using an event loop to listen to stream so we can sort of be non blocking
        //$loop = Looper::create();
        $final = "";
        if($call != null) {
            $this->call = $call;
            $request = new SpeechRequest();
            $request->setId(8);
            $this->call->write($request);
            /* This doesn't actually work. will have to implement some sort of timeout?
             * as read() still blocks everything else
             * $loop->addPeriodicTimer(1, function(){
             *   echo "listen";
             *   //listen to stream here?
             *   if($this->call!=null){
             *       if($response = $this->call->read()){
             *           echo $response->getTranscript();
             *           $this->connection->send($response->getTranscript());
             *       }
             *    }
             * });
             * $loop->run();
             */
        } else{
            while($responses = $this->call->read()){
                $transcript = $responses->getTranscript();
                if($transcript!="started") {
                    $final = $final ." ". $transcript ;
                    echo $transcript."!";
                    $this->connection->send($final);
                    break;
                }
                if($responses = $this->call->read()){
                    $transcript = $responses->getTranscript();
                    $this->connection->send($transcript);
                }
            }
        }
    }
}


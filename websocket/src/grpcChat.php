<?php
//whoever designed this garbage language should be shot
namespace ws;
use Backendservice\backendServiceClient;
use Backendservice\SpeechRequest;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Grpc;
class grpcChat implements MessageComponentInterface{
    protected  $clients;
    protected $client;
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->client = new backendServiceClient('localhost:8980', [
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
        //echo($msg);
        foreach ($this->clients as $wsclient){
            if($from == $wsclient->getConnection()){
                if($msg=="START"){
                    echo $msg. " ";
                    $wsclient->listen($this->client->getTest());
                }
                else if($msg=="COMPLETED"){
                    $wsclient->getCall()->writesDone();
                    $wsclient->listen(null);
                }
                else {
                    $request = new SpeechRequest();
                    $request->setAudioData($msg);
                    //echo $msg;
                    if($wsclient->getCall() != null) {
                        $wsclient->getCall()->write($request);
                    }
                }
            }
        }
        // TODO: Implement onMessage() method.
    }
    public function onClose(ConnectionInterface $conn)

    {
        foreach ($this->clients as $client){
            if($conn == $client->getConnection()){
                $client->getCall()->writesDone();
            }
    }
        // TODO: Implement onClose() method.
    }
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
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
        //to the stream and listening for responses to the stream.
        $final = "";
        if($call != null) {
            $this->call = $call;
            $request = new SpeechRequest();
            $request->setId(8);
            $this->call->write($request);
        } else{
            while($responses = $this->call->read()){
                $transcript = $responses->getTranscript();
                if($transcript!="started") {
                    $final = $final . $transcript . " ";
                    echo $transcript."!";
                    $this->connection->send($final);
                   // $this->call = null;
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

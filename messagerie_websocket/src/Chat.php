<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
session_start();
class Chat implements MessageComponentInterface {
 
        protected $clients;
        protected $rooms;
    
        public function __construct() {
            $this->clients = new \SplObjectStorage;
            $this->rooms = array();
        }
    
        public function onOpen(ConnectionInterface $conn)
        {
            //recuperer lid du client dans lurl de connexion
            $query = $conn->httpRequest->getUri()->getQuery();
            parse_str($query, $data);
            $id = $data['identifiant'];

            
           // definir lid du client comme etant l'id de la session
            $conn->resourceId = $id ;

            $this->clients->attach($conn);
            echo "New connection! ({$conn->resourceId})\n";
            // ...
        }
        
    
        public function onMessage(ConnectionInterface $from, $msg) {
            $data = json_decode($msg);

            if ($data->type === 'join') {
                
                //pouvoir appartenir a une seule room a la fois et si on change de room on quitte la precedente
                if(isset($from->room)){
                    $room = $from->room;
                    $this->rooms[$room]->detach($from);
                }
                $room = $data->room;
                $from->room = $room;
                if (!isset($this->rooms[$room])) {
                    $this->rooms[$room] = new \SplObjectStorage;
                }
                $this->rooms[$room]->attach($from);


                echo "Client ({$from->resourceId}) joined room: {$room}\n";
            } elseif ($data->type === 'message') {
                $room = $data->room;
                $message = $data->msg;
                $id = $data->id;
                $hour = $data->time;
            
                

               
                foreach ($this->rooms[$room] as $client) {
                    if ($from !== $client) {
                        //envoyer le message et l'id du client qui a envoye le message et lheure
                        $client->send(json_encode(array('type'=>'message', 'msg'=>$message, 'id'=>$id, 'time'=>$hour)));
                    
                        
                       
                    }
         //enfoyer le message a tous les clients de la room sauf le client qui a envoye le message
                }
            }

        }
        
    
    public function onClose(ConnectionInterface $conn) {
        session_destroy();
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
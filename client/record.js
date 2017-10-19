//js sux
//Brendan Chan
//Dragon Veterinary

var recordButton = document.getElementById("record");
var stopped = true;
var recording = false;
var streamstart; //keeps track of last voice heard/time of stream start
var text = "";

recordButton.addEventListener('click', function(){
    if(!recording){
        recordButton.innerText = "Stop";
        record();
    }
    else{
        recordButton.innerText = "Record";
        stopRecord();
    }
});
navigator.mediaDevices.getUserMedia({audio:true, video:false}).then(initialiseRecording);
function record(){
    recording = true;
    stopped =  false;
    ws.send("START");
    streamstart = Date.now();
}
function stopRecord(){
    stopped = true;
    ws.send("COMPLETED");
    recording = false;
}
var ws;
function setupWebsocket() {
    //sets up websocket connection and keeps it alive no matter what
    this.ws = new WebSocket("wss://localhost:8443");
    this.ws.onopen = function () {
        ws.send("ping");
        console.log("Connected to websocket");

    };
    this.ws.onmessage = function (e) {
        console.log(e.data);
        text = e.data;
        document.activeElement.textContent = document.activeElement.textContent + text;
    };
    this.ws.onclose = function () {
        console.log("closed");
        setupWebsocket();
    };
    this.ws.onerror = console.log("ERROR");
}
setupWebsocket();
function initialiseRecording(stream){
    var audio_context = new AudioContext;
    var sample_rate  =audio_context.sampleRate;
    console.log(audio_context.sampleRate);
    var buffer_size = 2048;
    var audio_input = audio_context.createMediaStreamSource(stream);
    var recorder = audio_context.createScriptProcessor(buffer_size, 1,1);
    recorder.onaudioprocess = processAudio;
    audio_input.connect(recorder);
    recorder.connect(audio_context.destination);
}

function processAudio(e){
    //send bytes of audio to php server using websocket
    if(recording){
        var left = e.inputBuffer.getChannelData(0);
        convertFloat32ToInt16(left);
    }
}
function convertFloat32ToInt16(buffer) {
    //convert float to int so it can be handled by backend
    l = buffer.length;
    buf = new Int16Array(l);
    var sum = 0;
    while (l--) {
        buf[l] = Math.min(1, buffer[l])*0x7FFF;
        sum+=buf[l];
    }
    //listen for breaks in speech to allow for receiving transcripts
    if(sum<900){
        console.log(Date.now()-streamstart);
        //if speech not heard for 2 seconds, stop sending audio and send completed signal
        //otherwise keep sending audio
        if(Date.now()-streamstart>1500 && !stopped){
            console.log("stop");
            stopped = true;
            ws.send("COMPLETED")
        }
        else{
            ws.send(buf.buffer);
        }
    }
    //speech heard, keep sending buffer
    else{
        if(stopped){
            stopped = false;
            ws.send("START");
        }
        streamstart = Date.now();
        ws.send(buf.buffer);
    }

}

    
    
    
    var bctaiChatStream;
    var bctaiChatRec;
    var bctaiInput;
    var bctaiChatAudioContext = window.AudioContext || window.webkitAudioContext;
    var bctaiaudioContext;
    let AIMsgIndex = 0;
    
    var bctaiMicBtns = document.querySelectorAll('.iconChatSpeak');
    var bctaiChatTyping = document.querySelectorAll('.textAreaBox');
    var bctaiChatSend = document.querySelectorAll('.iconChatTyping');
    var chatbox = document.getElementById('bctai-chatbox');
    
    let data_stt_value;
    let bctaiSTTinput = document.getElementById('textAreaBox');
    let bctaichat_content = document.getElementById('chatbot-contents');
    let bctaichat_message = document.getElementById('messages');
    let Menu_status;


    if (chatbox) {
        Menu_status = chatbox.getAttribute('data-Menu_status');
        // alert(Menu_status);
        if(Menu_status){
            document.addEventListener('DOMContentLoaded', function () {
                bctaiSTTinput.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight > 160 ? 160 : this.scrollHeight) + 'px';
                    if(this.scrollHeight <63){
                        bctaichat_content.style.padding = '0px 0px 177px 10px';
                        bctaichat_message.scrollTop = bctaichat_message.scrollHeight;
                    }else if(this.scrollHeight <82){
                        bctaichat_content.style.padding = '0px 0px 191px 10px';
                        bctaichat_message.scrollTop = bctaichat_message.scrollHeight;
                    }else if(this.scrollHeight <102){
                        bctaichat_content.style.padding = '0px 0px 211px 10px';
                        bctaichat_message.scrollTop = bctaichat_message.scrollHeight;
                    }else{
                        bctaichat_content.style.padding = '0px 0px 216px 10px';
                        bctaichat_message.scrollTop = bctaichat_message.scrollHeight;
                    }
                });
            });
        }else{
            document.addEventListener('DOMContentLoaded', function () {
                bctaiSTTinput.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight > 160 ? 160 : this.scrollHeight) + 'px';
                    if(this.scrollHeight <63){
                        bctaichat_content.style.padding = '0px 0px 124px 10px';
                        bctaichat_message.scrollTop = bctaichat_message.scrollHeight;
                    }else if(this.scrollHeight <82){
                        bctaichat_content.style.padding = '0px 0px 149px 10px';
                        bctaichat_message.scrollTop = bctaichat_message.scrollHeight;
                    }else if(this.scrollHeight <102){
                        bctaichat_content.style.padding = '0px 0px 152px 10px';
                        bctaichat_message.scrollTop = bctaichat_message.scrollHeight;
                    }else{
                        bctaichat_content.style.padding = '0px 0px 157px 10px';
                        bctaichat_message.scrollTop = bctaichat_message.scrollHeight;
                    }
                });
            });
        }

        
        welcom_MSG();
        data_stt_value = chatbox.getAttribute('data-stt-method');
    }
    
    const recognition = new window.webkitSpeechRecognition();
    const language = 'ko-KR';
    let isRecognizing = false;
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.maxAlternatives = 10000;
    recognition.onstart = function () {
        isRecognizing = true;
    };
    recognition.onend = function () {
        isRecognizing = false;
    };
    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        bctaiSTTinput.value = transcript;
    };

    function start(mic) {
        if (isRecognizing) {
            console.log("====종료====");
            mic.classList.remove('bctai-recording');
            recognition.stop();
            return;
        }
        console.log("====시작====");
        mic.classList.add('bctai-recording');
        recognition.lang = language;
        recognition.start();
    }
    function formatAMPM(date) {
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; 
        minutes = minutes < 10 ? '0' + minutes : minutes;
        var strTime = hours + ':' + minutes + ' ' + ampm;
        return strTime;
    }
    var date_Time = "";


    function bctaiescapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function bctaistartChatRecording() {
        let constraints = { audio: true, video: false }
        navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
            bctaiaudioContext = new bctaiChatAudioContext();
            bctaiChatStream = stream;
            bctaiInput = bctaiaudioContext.createMediaStreamSource(stream);
            bctaiChatRec = new Recorder(bctaiInput, { numChannels: 1 });
            bctaiChatRec.record();
        })
    }

    function bctaistopChatRecording(mic) {
        bctaiChatRec.stop();
        bctaiChatStream.getAudioTracks()[0].stop();
        bctaiChatRec.exportWAV(function (blob) {
            let type = mic.getAttribute('data-type');
            let parentChat;
            let chatContent;
            let chatTyping;
            if (type === 'widget') {
                parentChat = mic.closest('.bctai-chatbox');
                chatContent = parentChat.querySelectorAll('.chatbot-contents')[0];
                chatTyping = parentChat.querySelectorAll('.bctai-chat-widget-typing')[0];
            } else {
                parentChat = mic.closest('.bctai-chatbox');
                chatContent = parentChat.querySelectorAll('.chatbot-contents')[0];
                chatTyping = parentChat.querySelectorAll('.bctai-chat-shortcode-typing')[0];
            }
            console.log(blob);
            bctaiSendChatMessage(parentChat, chatTyping, type, blob);
        });
    }


    function SaveAnswer(){
        const txtElements = document.querySelectorAll('.txt');
        const lastTxtElement = txtElements[txtElements.length - 1];
        const lastTxtContent = lastTxtElement.textContent.trim();
        const audioElement = lastTxtElement.querySelector('audio');
        if (audioElement) {
            const blobUrl = audioElement.src;
            fetch(blobUrl)
            .then(response => response.blob())
            .then(blob => {
              
              bctaiSendChatMessage(chatbox, lastTxtContent, 'AskGPT',blob);
            });
        }else{
            bctaiSendChatMessage(chatbox, lastTxtContent, 'AskGPT');

        }
    }



    function bctaiSendChatMessage(chat, typing, type, blob) {
        date_Time = formatAMPM(new Date());
        let bctai_box_typing = typing;

        // console.log(blob);
        let bctai_ai_thinking, bctai_messages_box, class_user_item, class_ai_item;
        let bctaiMessage = '';
        let bctaiData = new FormData();
        let bctai_ai_name = chat.getAttribute('data-ai-name');
        let bctai_nonce = chat.getAttribute('data-nonce');
        //let bctai_bot_id = parseInt(chat.getAttribute('data-bot-id'));
        let bctai_ai_avatar = chat.getAttribute('data-ai-avatar');
        let bctai_speech = chat.getAttribute('data-speech');
        let bctai_voice = chat.getAttribute('data-voice');
        let bctai_voice_error = chat.getAttribute('data-voice-error');
        let url = chat.getAttribute('data-url');
        let post_id = chat.getAttribute('data-post-id');
        let act_as = chat.getAttribute('data-act-as');
        let user_id = chat.getAttribute('data-user-id');
        let voice_service = chat.getAttribute('data-voice_service');
        let voice_language = chat.getAttribute('data-voice_language');
        let voice_name = chat.getAttribute('data-voice_name');
        let voice_device = chat.getAttribute('data-voice_device');
        let voice_speed = chat.getAttribute('data-voice_speed');
        let voice_pitch = chat.getAttribute('data-voice_pitch');
        let bctaichat_content = document.getElementById('chatbot-contents');
        let UploadImgUrl = document.getElementById('UploadImgUrl');
        Scenario_status = chatbox.getAttribute('data-Scenario_status');

        
        bctaiData.append('UploadImgUrl',UploadImgUrl.value);
        
        

        if (type === 'widget') {
            bctai_messages_box = chat.getElementsByClassName('messages')[0];
        }else if(type === 'AskGPT'){
            bctai_messages_box = chat.getElementsByClassName('messages')[0];
        }else {
            bctai_messages_box = chat.getElementsByClassName('messages')[0];
        }


        if(Scenario_status){
            bctaichat_content.style.padding = '0px 0px 124px 10px';
        }else{
            bctaichat_content.style.padding = '0px 0px 124px 10px';
        }


        
        let bctai_question;
        if(type === 'AskGPT'){
            bctai_question = bctai_box_typing;
        } else{
            if(!blob){
                bctai_question = bctaiescapeHtml(bctai_box_typing.value);
            }
        }
        
        bctaiMessage += "<div style='width: 100%; float: right; margin-top:10px;'><div class='message right archive'><span class='name'><i class='cbiCon'></i></span><div class='bubble'><div class='txt'>";
        bctaiData.append('_wpnonce', bctai_nonce);
        bctaiData.append('act_as', act_as);
        bctaiData.append('post_id', post_id);
        bctaiData.append('user_id', user_id);
        bctaiData.append('url', url);
        bctaiData.append('action', 'bctai_chatbox_message');

        if(type === 'AskGPT'){
            bctaiData.append('AskGPT', true);
        }


        if (blob !== undefined) {
            let url = URL.createObjectURL(blob);
            console.log(url);
            bctaiMessage += '<audio src="' + url + '" controls="true"></audio>';
            bctaiData.append('audio', blob, 'bctai-chat-recording.wav');
        } else if (bctai_question !== '') {
            bctaiMessage += bctai_question.replace(/\n/g, '<br>');
            var jsonData = JSON.stringify(bctai_question);
            bctaiData.append('message', bctai_question);
        }
        
        bctaiMessage += "</div></div><div class='date'>" + date_Time + "</div></div></div>";
        bctai_messages_box.innerHTML += bctaiMessage;
        bctai_messages_box.scrollTop = bctai_messages_box.scrollHeight;


        
        const xhttp = new XMLHttpRequest();
        if(!blob){
            bctai_box_typing.value = '';
        }

        bctaiSTTinput.style.height = '100%';
        if(Scenario_status){
            bctaichat_content.style.padding = '0px 0px 124px 10px';
        }else{
            bctaichat_content.style.padding = '0px 0px 124px 10px';
        }
        xhttp.open('POST', bctai_ajax_url, true);
        //alert(bctai_ajax_url);
        // console.log(bctaiData);
        xhttp.send(bctaiData);
        xhttp.onreadystatechange = function (oEvent) {
            //alert(oEvent);
            if (xhttp.readyState === 4) {
                var bctai_message = '';
                var bctai_response_text = '';
                var bctai_response_url = '';
                var bctai_img_url ='';
                var bctai_randomnum = '';
                var cosine_score = '';
                var bctai_link_url ='';

                // var bctai_randomnum = Math.floor((Math.random() * 100000) + 1);


                if (xhttp.status === 200) {
                    var bctai_response = this.responseText;
                    console.log(bctai_response);
                    
                    
                    if (bctai_response !== '') {
                        bctai_response = JSON.parse(bctai_response);
                        console.log(bctai_response.data);
                        if(Scenario_status){
                            bctaichat_content.style.padding = '0px 0px 124px 10px';
                        }else{
                            bctaichat_content.style.padding = '0px 0px 124px 10px';
                        }
                        // bctai_ai_thinking.style.display = 'none'
                        if (bctai_response.status === 'success'){

                            bctai_response_text = bctai_response.data;
                            bctai_img_url = bctai_response.ImgURL;
                            bctai_response_url = bctai_response.url;
                            cosine_score = bctai_response.cosine_score;

                            bctai_link_url = bctai_response.bctai_link_url;

                            

                            let bctai_response_greetingmessage = bctai_response.greeting_message;
                            console.log(bctai_response_greetingmessage);


                            bctai_message += "<div style='width: 100%; float: left; margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i> " + bctai_ai_name + "</span><div class='bubble'>";
                            //bctai_message += bctai_link_url;

                            // if(bctai_response_url){
                            //     bctai_randomnum += "<a href=' "+bctai_response_url+"' target='_blank'>"+bctai_response_url+"</a>";
                            // }

                            if(bctai_img_url){
                                bctai_message += "<img src=' "+bctai_img_url+"' height='200' width='200'></img>";
                            }
                            

                            bctai_message += "<div class='txt" + AIMsgIndex + "'></div>";
                        } else if(bctai_response.status === 'SaveAnswer'){

                            bctai_response_text = bctai_response.data;
                            bctai_message = "<div style='width: 100%; float: left;margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i> " + bctai_ai_name + "</span><div class='bubble'><div class='txt" + AIMsgIndex + "'></div> <button type='button' onclick='SaveAnswer()'>계속하기</button>";


                        }else {
                            bctai_response_text = "이해에 도움이 되지 않았거나 추가적인 정보가 필요하신 경우, 언제든지 질문하거나 도움을 요청해 주십시오. 원하는 내용에 대해 자세히 설명해 드릴 수 있습니다!";
                            bctai_message = "<div style='width: 100%; float: left;margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i> " + bctai_ai_name + "</span><div class='bubble'><div class='txt" + AIMsgIndex + "'></div>";
                        }
                    }
                }
                else {
                    bctai_message = "<div style='width: 100%; float: left; margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i> " + bctai_ai_name + "</span><div class='bubble'><div class='txt'>" + bctai_response_text + "</div>";
                    bctai_response_text = 'Something went wrong5';
                }
                if (bctai_response_text === 'null' || bctai_response_text === null) {
                    bctai_response_text = 'The model predicted a completion that begins with a stop sequence, resulting in no output. Consider adjusting your prompt or stop sequences.';
                }
                if (bctai_response_text !== '' && bctai_message !== '') {
                    if (parseInt(bctai_speech) == 1) {
                        if (voice_service === 'google') {
                            //alert(voice_service);
                            // bctai_ai_thinking.style.display = 'block';
                            if(Scenario_status){
                                bctaichat_content.style.padding = '0px 0px 124px 10px';
                            }else{
                                bctaichat_content.style.padding = '0px 0px 124px 10px';
                            }
                            let speechData = new FormData();
                            speechData.append('type', type);
                            speechData.append('nonce', bctai_nonce);
                            speechData.append('action', 'bctai_google_speech');
                            speechData.append('language', voice_language);
                            speechData.append('name', voice_name);
                            speechData.append('device', voice_device);
                            speechData.append('speed', voice_speed);
                            speechData.append('pitch', voice_pitch);
                            speechData.append('text', bctai_response_text);
                            var speechRequest = new XMLHttpRequest();
                            speechRequest.open("POST", bctai_ajax_url);
                            speechRequest.onload = function () {
                                var result = speechRequest.responseText;
                                
                                try {
                                    result = JSON.parse(result);
                                    // console.log(result);
                                    //alert(JSON.stringify(result));
                                    if (result.status === 'success') {
                                        var byteCharacters = atob(result.audio);
                                        const byteNumbers = new Array(byteCharacters.length);
                                        for (let i = 0; i < byteCharacters.length; i++) {
                                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                                        }
                                        const byteArray = new Uint8Array(byteNumbers);
                                        const blob = new Blob([byteArray], { type: 'audio/mp3' });
                                        const blobUrl = URL.createObjectURL(blob);
                                        //console.log(blobUrl);
                                        bctai_message += '<audio style="margin-top:2px;width: 100% " controls="controls"><source type="audio/mpeg" src="' + blobUrl + '"></audio>';
                                        bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
                                        if(Scenario_status){
                                            bctaichat_content.style.padding = '0px 0px 124px 10px';
                                        }else{
                                            bctaichat_content.style.padding = '0px 0px 124px 10px';
                                        }
                                        // bctai_ai_thinking.style.display = 'none';
                                        
                                        bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text);
                                    }
                                    else {
                                        var errorMessageDetail = 'Google: ' + result.msg;
                                        if(Scenario_status){
                                            bctaichat_content.style.padding = '0px 0px 124px 10px';
                                        }else{
                                            bctaichat_content.style.padding = '0px 0px 124px 10px';
                                        }
                                        // bctai_ai_thinking.style.display = 'none';
                                        
                                        if (parseInt(bctai_voice_error) !== 1) {
                                            bctai_message += '<span style="width: 100%;display: block;font-size: 11px;">' + errorMessageDetail + '</span>';
                                        }
                                        else if (typeof bctai_response !== 'undefined' && typeof bctai_response.log !== 'undefined' && bctai_response.log !== '') {
                                            var speechLogMessage = new FormData();
                                            speechLogMessage.append('nonce', bctai_nonce);
                                            speechLogMessage.append('log_id', bctai_response.log);
                                            speechLogMessage.append('message', errorMessageDetail);
                                            speechLogMessage.append('action', 'bctai_speech_error_log');
                                            var speechErrorRequest = new XMLHttpRequest();
                                            speechErrorRequest.open("POST", bctai_ajax_url);
                                            speechErrorRequest.send(speechLogMessage);
                                        }
                                        bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
                                        bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text);
                                    }
                                }
                                catch (errorSpeech) {

                                }
                            }
                            speechRequest.send(speechData);
                        }
                        else {
                            alert(voice_service);
                            //elevenlabs

                            let speechData = new FormData();
                            speechData.append('nonce', bctai_nonce);
                            speechData.append('message', bctai_response_text);
                            speechData.append('voice', bctai_voice);
                            speechData.append('action', 'bctai_text_to_speech');
                            // bctai_ai_thinking.style.display = 'block';
                            if(Scenario_status){
                                bctaichat_content.style.padding = '0px 0px 124px 10px';
                            }else{
                                bctaichat_content.style.padding = '0px 0px 124px 10px';
                            }
                            var speechRequest = new XMLHttpRequest();
                            speechRequest.open("POST", bctai_ajax_url);
                            speechRequest.responseType = "arraybuffer";
                            speechRequest.onload = function () {
                                if(Scenario_status){
                                    bctaichat_content.style.padding = '0px 0px 124px 10px';
                                }else{
                                    bctaichat_content.style.padding = '0px 0px 124px 10px';
                                }
                                // bctai_ai_thinking.style.display = 'none';
                                
                                var blob = new Blob([speechRequest.response], { type: "audio/mpeg" });
                                var fr = new FileReader();
                                fr.onload = function () {
                                    var fileText = this.result;
                                    try {
                                        var errorMessage = JSON.parse(fileText);
                                        var errorMessageDetail = 'ElevenLabs: ' + errorMessage.detail.message;
                                        if (parseInt(bctai_voice_error) !== 1) {
                                            bctai_message += '<span style="width: 100%;display: block;font-size: 11px;">' + errorMessageDetail + '</span>';
                                        } else if (typeof bctai_response !== 'undefined' && typeof bctai_response.log !== 'undefined' && bctai_response.log !== '') {
                                            var speechLogMessage = new FormData();
                                            speechLogMessage.append('nonce', bctai_nonce);
                                            speechLogMessage.append('log_id', bctai_response.log);
                                            speechLogMessage.append('message', errorMessageDetail);
                                            speechLogMessage.append('action', 'bctai_speech_error_log');
                                            var speechErrorRequest = new XMLHttpRequest();
                                            speechErrorRequest.open("POST", bctai_ajax_url);
                                            speechErrorRequest.send(speechLogMessage);
                                        }
                                        bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
                                        bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text);
                                    } catch (errorBlob) {
                                        var blobUrl = URL.createObjectURL(blob);
                                        bctai_message += '<audio style="margin-top:2px;width: 100%" controls="controls"><source type="audio/mpeg" src="' + blobUrl + '"></audio>';
                                        bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
                                        bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text);
                                    }
                                }
                                fr.readAsText(blob);
                            }
                            speechRequest.send(speechData);

                        }
                    }
                    else {
                        bctai_message += "</div><div class='date'>" + date_Time + "</div></div></div>";

                        //alert(bctai_message);
                        
                        bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text,cosine_score,bctai_link_url);

                    }
                }
            }
        }
    }


    



    function welcom_MSG() {
        date_Time = formatAMPM(new Date());
        var bctai_messages_box = document.getElementsByClassName('messages')[0];
        let parentChat = document.getElementsByClassName('bctai-chatbox')[0];
        let bctai_ai_avatar = parentChat.getAttribute('data-ai-avatar');
        let bctai_ai_name = parentChat.getAttribute('data-ai-name');
        
        var bctai_randomnum = '';
        var cosine_score ='';

        var input_val = parentChat.getAttribute('data-welcome-message');
        var bctai_response_text = input_val;
        var bctai_message = "";
        
        
        // bctai_message += "<div style='width: 100%; float: left; margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i>" + bctai_ai_name + "</span><div class='bubble'>";
        // bctai_message += "<div class='txt" + AIMsgIndex + "'></div>";

        
        bctai_message += "<div style='width: 100%; float: left; margin-top:10px;'><div class='message left'><span class='name'><img src='" + bctai_ai_avatar + "' height='40' width='40'></img><i class='cbiCon'></i>" + bctai_ai_name + "</span><div class='bubble'> <div class='txt" + AIMsgIndex + "'></div>";

        if (bctai_response_text !== '' && bctai_message !== '') {
            bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
            bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text,cosine_score);
        } else {
            bctai_message += "</div><div class='date'>" + date_Time + "</div></div>";
            bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text);
        }
    }


    



    function bctaiWriteMessage(bctai_messages_box, bctai_message, bctai_randomnum, bctai_response_text, cosine_score,bctai_link_url) {
        bctai_messages_box.innerHTML += bctai_message;
        const content = bctai_response_text;
        const text = document.querySelector(".chatbot-contents .message.left .bubble .txt" + AIMsgIndex);
        bctai_messages_box.scrollTop = bctai_messages_box.scrollHeight;
        // console.log(content);

        for (var i = 0; i < content.length; i++) {
            (function (i) {
                setTimeout(function () {
                    text.innerHTML += content[i];
                    if(content[i]==='\n'){
                        text.innerHTML += "<br>";
                    }

                    if (i === content.length - 1) {
                        
                        text.innerHTML +=bctai_randomnum;
                        if(cosine_score){
                            //text.innerHTML += '<br> (코사인유사도 : '+cosine_score+' %)';
                        }
                        if(bctai_link_url){
                            text.innerHTML += bctai_link_url;
                        }
                        bctai_messages_box.scrollTop = bctai_messages_box.scrollHeight;
                    }

                }, i * 50); 
            })(i);
        }

        

        AIMsgIndex++;
        
        
        var i = 0;
        var bctai_speed = 20;

        function bctaiLinkify(inputText) {
            var replacedText, replacePattern1, replacePattern2, replacePattern3;

            //URLs starting with http://, https://, or ftp://
            replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
            replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank">$1</a>');

            //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
            replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
            replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank">$2</a>');

            //Change email addresses to mailto:: links.
            replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
            replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');

            return replacedText;
        }
        if (bctai_response_text !== '') {
            bctai_response_text = bctai_response_text.trim();
        }
        bctai_response_text = bctai_response_text.replace(/\n/g, '≈');




        
        function bctai_typeWriter() {

            if (i < bctai_response_text.length) {
                if (bctai_response_text.charAt(i) === '≈') {
                    bctai_current_message.innerHTML += '<br>';
                }
                else {
                    bctai_current_message.innerHTML += bctai_response_text.charAt(i);
                }
                i++;
                setTimeout(bctai_typeWriter, bctai_speed);
                bctai_messages_box.scrollTop = bctai_messages_box.scrollHeight;
            } else {
                bctai_current_message.innerHTML = bctaiLinkify(bctai_current_message.innerHTML);
                bctai_current_message.innerHTML = bctai_current_message.innerHTML.replace(/```([\s\S]*?)```/g, '<code>$1</code>');
            }
        }
        //bctai_typeWriter();
    }

    function bctaiMicEvent(mic) {
        if (mic.classList.contains('bctai-recording')) {
            mic.classList.remove('bctai-recording');
            console.log(mic);
            bctaistopChatRecording(mic)
        } else {
            //alert('startChat');
            let checkRecording = document.querySelectorAll('.bctai-recording');
            if (checkRecording && checkRecording.length) {
                alert('Please finish previous recording');
            } else {
                mic.classList.add('bctai-recording');
                bctaistartChatRecording();
            }
        }
    }

    if (bctaiChatTyping && bctaiChatTyping.length) {
        for (let i = 0; i < bctaiChatTyping.length; i++) {
            bctaiChatTyping[i].addEventListener('keyup', function (event) {
                if (event.shiftKey && (event.which === 13 || event.keyCode === 13)) {
                }
                else if (!event.shiftKey && (event.which === 13 || event.keyCode === 13)) {
                    let parentChat = bctaiChatTyping[i].closest('.bctai-chatbox');
                    let chatTyping = parentChat.querySelectorAll('.textAreaBox')[0];
                    
                    bctaiSendChatMessage(parentChat, chatTyping, 'widget');
                    event.preventDefault();
                }
            })
        }
    }

    if (bctaiChatSend && bctaiChatSend.length) {
        for (let i = 0; i < bctaiChatSend.length; i++) {
            bctaiChatSend[i].addEventListener('click', function (event) {
                let parentChat = bctaiChatSend[i].closest('.bctai-chatbox');
                let chatTyping = parentChat.querySelectorAll('.textAreaBox')[0];
                bctaiSendChatMessage(parentChat, chatTyping, 'widget');
            })
        }
    }

    

        if (bctaiMicBtns && bctaiMicBtns.length) {
    
            for (let i = 0; i < bctaiMicBtns.length; i++) {
                bctaiMicBtns[i].addEventListener('click', function () {
                    // alert(data_stt_value);

                    if(data_stt_value=='Audio'){
                        bctaiMicEvent(bctaiMicBtns[i]);
                    }else{
                        start(bctaiMicBtns[i]);
                    }
                }); 
            }
        }
    


        function removeChatlog(){
            // alert("dldkdk");
            jQuery('.messages').empty();
            welcom_MSG();   

        }

    
    
    var isExpanded = false;
    jQuery('.btn-fullscreen').click(function () {
        jQuery(this).find('i').toggleClass('fa-expand fa-compress');
        if (isExpanded) {
            jQuery('.high, .bctai_chat_widget_content').css({
                'height': '',
                'width': ''  
            });
        } else {
            jQuery('.high, .bctai_chat_widget_content').css({
                'height': '690px', 
                'width': '1335px'   
            });
            jQuery('.high').css({
                'height': '690px', 
            });
        }
    
        // 상태를 변경
        isExpanded = !isExpanded;
    });

    jQuery('.btn-mail').click(function () {
        jQuery('#popup-dialog').dialog({
            modal: true,
            width: 'auto',
            resizable: false,
            position: { my: "center", at: "center", of: window },
        });
        
        jQuery('.ui-front').css('z-index', '2001');
        jQuery('.ui-dialog').css('z-index', '2002');


        jQuery('.ui-draggable').css('width', '1066px');
        jQuery('.ui-draggable').css('height', '673px');
        jQuery('.ui-draggable').css('background', '#FFFFFF 0% 0% no-repeat padding-box');
        jQuery('.ui-draggable').css('border-radius', '30px');
        jQuery('.ui-draggable').css('overflow', 'hidden');
        jQuery('.ui-draggable').css('padding', '20px');
        jQuery('.ui-draggable').css('box-shadow', '0px 0px 10px 3px gray');

        jQuery('.ui-draggable-handle').css('background', 'none');
        jQuery('.ui-draggable-handle').css('border', '0px');
        jQuery('.ui-draggable-handle').css('font-size', '24px');
        jQuery('.ui-draggable-handle').css('font-weight', '900');

        
    });


    // $('.iconFile').click(function (e) {
    //     e.preventDefault();
    //     // var button = $(e.currentTarget),
    //     custom_uploader = wp.media({
    //         title: 'Insert image',
    //         library : {
    //             type : 'image'
    //         },
    //         button: {
    //             text: 'Use this image'
    //         },
    //         multiple: false
    //     }).on('select', function() {
    //         var attachment = custom_uploader.state().get('selection').first().toJSON();
    //         console.log(attachment);
    //         var imgTag = '<div class="message right"><div class="bubble"><img style="width:100%;"src="' + attachment.url + '" alt="Image"></div></div>';
    //         $('#messages').append(imgTag);

    //         var bctai_messages_box = document.getElementById('messages');
    //         bctai_messages_box.scrollTop = bctai_messages_box.scrollHeight;

    //         $('.UploadImgUrl').val(attachment.url);
    //     }).open();
    // });
    

    
    $('#fileInput').on('change', function(event) {
        const file = this.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'uploadtest');
            formData.append('name', '테스트');
    
            $.ajax({
                url: bctai_ajax_url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    alert(response.file_url);
                    $('.UploadImgUrl').val(response.file_url);
                    var imgTag = '<div class="message right"><div class="bubble"><img style="width:100%;"src="' + response.file_url + '" alt="Image"></div></div>';
                    $('#messages').append(imgTag);
                },
                error: function(xhr, status, error) {
                    console.error('파일 업로드 실패:', error);
                }
            });
        }
    });
    









(function (f) { if (typeof exports === "object" && typeof module !== "undefined") { module.exports = f() } else if (typeof define === "function" && define.amd) { define([], f) } else { var g; if (typeof window !== "undefined") { g = window } else if (typeof global !== "undefined") { g = global } else if (typeof self !== "undefined") { g = self } else { g = this } g.Recorder = f() } })(function () {
    var define, module, exports; return (function e(t, n, r) { function s(o, u) { if (!n[o]) { if (!t[o]) { var a = typeof require == "function" && require; if (!u && a) return a(o, !0); if (i) return i(o, !0); var f = new Error("Cannot find module '" + o + "'"); throw f.code = "MODULE_NOT_FOUND", f } var l = n[o] = { exports: {} }; t[o][0].call(l.exports, function (e) { var n = t[o][1][e]; return s(n ? n : e) }, l, l.exports, e, t, n, r) } return n[o].exports } var i = typeof require == "function" && require; for (var o = 0; o < r.length; o++)s(r[o]); return s })({
        1: [function (require, module, exports) {
            "use strict";

            module.exports = require("./recorder").Recorder;

        }, { "./recorder": 2 }], 2: [function (require, module, exports) {
            'use strict';

            var _createClass = (function () {
                function defineProperties(target, props) {
                    for (var i = 0; i < props.length; i++) {
                        var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor);
                    }
                } return function (Constructor, protoProps, staticProps) {
                    if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor;
                };
            })();

            Object.defineProperty(exports, "__esModule", {
                value: true
            });
            exports.Recorder = undefined;

            var _inlineWorker = require('inline-worker');

            var _inlineWorker2 = _interopRequireDefault(_inlineWorker);

            function _interopRequireDefault(obj) {
                return obj && obj.__esModule ? obj : { default: obj };
            }

            function _classCallCheck(instance, Constructor) {
                if (!(instance instanceof Constructor)) {
                    throw new TypeError("Cannot call a class as a function");
                }
            }

            var Recorder = exports.Recorder = (function () {
                function Recorder(source, cfg) {
                    var _this = this;

                    _classCallCheck(this, Recorder);

                    this.config = {
                        bufferLen: 4096,
                        numChannels: 2,
                        mimeType: 'audio/wav'
                    };
                    this.recording = false;
                    this.callbacks = {
                        getBuffer: [],
                        exportWAV: []
                    };

                    Object.assign(this.config, cfg);
                    this.context = source.context;
                    this.node = (this.context.createScriptProcessor || this.context.createJavaScriptNode).call(this.context, this.config.bufferLen, this.config.numChannels, this.config.numChannels);

                    this.node.onaudioprocess = function (e) {
                        if (!_this.recording) return;

                        var buffer = [];
                        for (var channel = 0; channel < _this.config.numChannels; channel++) {
                            buffer.push(e.inputBuffer.getChannelData(channel));
                        }
                        _this.worker.postMessage({
                            command: 'record',
                            buffer: buffer
                        });
                    };

                    source.connect(this.node);
                    this.node.connect(this.context.destination); //this should not be necessary

                    var self = {};
                    this.worker = new _inlineWorker2.default(function () {
                        var recLength = 0,
                            recBuffers = [],
                            sampleRate = undefined,
                            numChannels = undefined;

                        self.onmessage = function (e) {
                            switch (e.data.command) {
                                case 'init':
                                    init(e.data.config);
                                    break;
                                case 'record':
                                    record(e.data.buffer);
                                    break;
                                case 'exportWAV':
                                    exportWAV(e.data.type);
                                    break;
                                case 'getBuffer':
                                    getBuffer();
                                    break;
                                case 'clear':
                                    clear();
                                    break;
                            }
                        };

                        function init(config) {
                            sampleRate = config.sampleRate;
                            numChannels = config.numChannels;
                            initBuffers();
                        }

                        function record(inputBuffer) {
                            for (var channel = 0; channel < numChannels; channel++) {
                                recBuffers[channel].push(inputBuffer[channel]);
                            }
                            recLength += inputBuffer[0].length;
                        }

                        function exportWAV(type) {
                            var buffers = [];
                            for (var channel = 0; channel < numChannels; channel++) {
                                buffers.push(mergeBuffers(recBuffers[channel], recLength));
                            }
                            var interleaved = undefined;
                            if (numChannels === 2) {
                                interleaved = interleave(buffers[0], buffers[1]);
                            } else {
                                interleaved = buffers[0];
                            }
                            var dataview = encodeWAV(interleaved);
                            var audioBlob = new Blob([dataview], { type: type });

                            self.postMessage({ command: 'exportWAV', data: audioBlob });
                        }

                        function getBuffer() {
                            var buffers = [];
                            for (var channel = 0; channel < numChannels; channel++) {
                                buffers.push(mergeBuffers(recBuffers[channel], recLength));
                            }
                            self.postMessage({ command: 'getBuffer', data: buffers });
                        }

                        function clear() {
                            recLength = 0;
                            recBuffers = [];
                            initBuffers();
                        }

                        function initBuffers() {
                            for (var channel = 0; channel < numChannels; channel++) {
                                recBuffers[channel] = [];
                            }
                        }

                        function mergeBuffers(recBuffers, recLength) {
                            var result = new Float32Array(recLength);
                            var offset = 0;
                            for (var i = 0; i < recBuffers.length; i++) {
                                result.set(recBuffers[i], offset);
                                offset += recBuffers[i].length;
                            }
                            return result;
                        }

                        function interleave(inputL, inputR) {
                            var length = inputL.length + inputR.length;
                            var result = new Float32Array(length);

                            var index = 0,
                                inputIndex = 0;

                            while (index < length) {
                                result[index++] = inputL[inputIndex];
                                result[index++] = inputR[inputIndex];
                                inputIndex++;
                            }
                            return result;
                        }

                        function floatTo16BitPCM(output, offset, input) {
                            for (var i = 0; i < input.length; i++, offset += 2) {
                                var s = Math.max(-1, Math.min(1, input[i]));
                                output.setInt16(offset, s < 0 ? s * 0x8000 : s * 0x7FFF, true);
                            }
                        }

                        function writeString(view, offset, string) {
                            for (var i = 0; i < string.length; i++) {
                                view.setUint8(offset + i, string.charCodeAt(i));
                            }
                        }

                        function encodeWAV(samples) {
                            var buffer = new ArrayBuffer(44 + samples.length * 2);
                            var view = new DataView(buffer);

                            /* RIFF identifier */
                            writeString(view, 0, 'RIFF');
                            /* RIFF chunk length */
                            view.setUint32(4, 36 + samples.length * 2, true);
                            /* RIFF type */
                            writeString(view, 8, 'WAVE');
                            /* format chunk identifier */
                            writeString(view, 12, 'fmt ');
                            /* format chunk length */
                            view.setUint32(16, 16, true);
                            /* sample format (raw) */
                            view.setUint16(20, 1, true);
                            /* channel count */
                            view.setUint16(22, numChannels, true);
                            /* sample rate */
                            view.setUint32(24, sampleRate, true);
                            /* byte rate (sample rate * block align) */
                            view.setUint32(28, sampleRate * 4, true);
                            /* block align (channel count * bytes per sample) */
                            view.setUint16(32, numChannels * 2, true);
                            /* bits per sample */
                            view.setUint16(34, 16, true);
                            /* data chunk identifier */
                            writeString(view, 36, 'data');
                            /* data chunk length */
                            view.setUint32(40, samples.length * 2, true);

                            floatTo16BitPCM(view, 44, samples);

                            return view;
                        }
                    }, self);

                    this.worker.postMessage({
                        command: 'init',
                        config: {
                            sampleRate: this.context.sampleRate,
                            numChannels: this.config.numChannels
                        }
                    });

                    this.worker.onmessage = function (e) {
                        var cb = _this.callbacks[e.data.command].pop();
                        if (typeof cb == 'function') {
                            cb(e.data.data);
                        }
                    };
                }

                _createClass(Recorder, [{
                    key: 'record',
                    value: function record() {
                        this.recording = true;
                    }
                }, {
                    key: 'stop',
                    value: function stop() {
                        this.recording = false;
                    }
                }, {
                    key: 'clear',
                    value: function clear() {
                        this.worker.postMessage({ command: 'clear' });
                    }
                }, {
                    key: 'getBuffer',
                    value: function getBuffer(cb) {
                        cb = cb || this.config.callback;
                        if (!cb) throw new Error('Callback not set');

                        this.callbacks.getBuffer.push(cb);

                        this.worker.postMessage({ command: 'getBuffer' });
                    }
                }, {
                    key: 'exportWAV',
                    value: function exportWAV(cb, mimeType) {
                        mimeType = mimeType || this.config.mimeType;
                        cb = cb || this.config.callback;
                        if (!cb) throw new Error('Callback not set');

                        this.callbacks.exportWAV.push(cb);

                        this.worker.postMessage({
                            command: 'exportWAV',
                            type: mimeType
                        });
                    }
                }], [{
                    key: 'forceDownload',
                    value: function forceDownload(blob, filename) {
                        var url = (window.URL || window.webkitURL).createObjectURL(blob);
                        var link = window.document.createElement('a');
                        link.href = url;
                        link.download = filename || 'output.wav';
                        var click = document.createEvent("Event");
                        click.initEvent("click", true, true);
                        link.dispatchEvent(click);
                    }
                }]);

                return Recorder;
            })();

            exports.default = Recorder;

        }, { "inline-worker": 3 }], 3: [function (require, module, exports) {
            "use strict";

            module.exports = require("./inline-worker");
        }, { "./inline-worker": 4 }], 4: [function (require, module, exports) {
            (function (global) {
                "use strict";

                var _createClass = (function () { function defineProperties(target, props) { for (var key in props) { var prop = props[key]; prop.configurable = true; if (prop.value) prop.writable = true; } Object.defineProperties(target, props); } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

                var _classCallCheck = function (instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } };

                var WORKER_ENABLED = !!(global === global.window && global.URL && global.Blob && global.Worker);

                var InlineWorker = (function () {
                    function InlineWorker(func, self) {
                        var _this = this;

                        _classCallCheck(this, InlineWorker);

                        if (WORKER_ENABLED) {
                            var functionBody = func.toString().trim().match(/^function\s*\w*\s*\([\w\s,]*\)\s*{([\w\W]*?)}$/)[1];
                            var url = global.URL.createObjectURL(new global.Blob([functionBody], { type: "text/javascript" }));

                            return new global.Worker(url);
                        }

                        this.self = self;
                        this.self.postMessage = function (data) {
                            setTimeout(function () {
                                _this.onmessage({ data: data });
                            }, 0);
                        };

                        setTimeout(function () {
                            func.call(self);
                        }, 0);
                    }

                    _createClass(InlineWorker, {
                        postMessage: {
                            value: function postMessage(data) {
                                var _this = this;

                                setTimeout(function () {
                                    _this.self.onmessage({ data: data });
                                }, 0);
                            }
                        }
                    });

                    return InlineWorker;
                })();

                module.exports = InlineWorker;
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {}]
    }, {}, [1])(1)
});

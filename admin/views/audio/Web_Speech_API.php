<?php
if (!defined('ABSPATH'))
    exit;
wp_enqueue_editor();
?>
<style>
.mic {
    width: 187px;
    height: 58px;
    background: #8040AD 0% 0% no-repeat padding-box;
    border-radius: 16px;
    opacity: 1;
    border:0px;
    margin-top:20px;
    font: normal normal bold 14px/20px Noto Sans KR;
    letter-spacing: 0px;
    color: #FFFFFF;
    opacity: 1;
}

.recognized-textarea {
    width: 1599px;
    height: 149px;
    background: #F1F1F1 0% 0% no-repeat padding-box;
    border-radius: 16px;
    opacity: 1;
   
}

</style>
<h1 style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Web speech', 'bctai') ?></h1>


<div class="contents">
    <p style="font: normal normal 300 16px/24px Noto Sans KR;letter-spacing: 0px;color: #352F39;"><?php echo __('Press button and Test', 'bctai') ?></p>


    <div class="recognized-textarea">
        <span class="final" id="final_span"></span>
        <span class="interim" id="interim_span"></span>
    </div>

    <div class="Button-area">
        <button class="mic"><?php echo __('TEST', 'bctai') ?>
            <span id="recording-state"></span>
        </button>
    </div>

</div>




<script>
    window.onload = function () {

        const recognition = new window.webkitSpeechRecognition();
        const language = 'ko-KR';
        const micBtn = document.querySelector('.mic');
        const delBtn = document.querySelector('.remove');
        const resultWrap = document.querySelector('.result');
        const recognizedTextarea = document.querySelector('.recognized-textarea');
        const recording_state = document.querySelector('#recording-state');

        const final_span = document.querySelector('#final_span');
        const interim_span = document.querySelector('#interim_span');

        let isRecognizing = false;
        let ignoreEndProcess = false;
        let finalTranscript = '';

        recognition.continuous = true;
        recognition.interimResults = true;


        //녹음 시작
        recognition.onstart = function (event) {
            console.log('onstart', event);
            isRecognizing = true;
            recording_state.className = 'on';
        };

        //녹음 종료
        recognition.onend = function () {
            console.log('onend', arguments);
            isRecognizing = false;

            if (ignoreEndProcess) {
                return false;
            }

            recording_state.className = 'off';
            console.log('off')
            if (!finalTranscript) {
                console.log('empty finalTranscript');
                return false;
            }
        };

        recognition.onresult = function (event) {
            console.log('onresult', event);

            let finalTranscript = '';
            let interimTranscript = '';
            if (typeof event.results === 'undefined') {
                recognition.onend = null;
                recognition.stop();
                return;
            }

            for (let i = event.resultIndex; i < event.results.length; ++i) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcript;
                } else {
                    interimTranscript += transcript;
                }
            }

            final_span.innerHTML = finalTranscript;
            interim_span.innerHTML = interimTranscript;
            final_span_Handler();

            console.log('finalTranscript', finalTranscript);
            console.log('interimTranscript', interimTranscript);
        };

        recognition.onerror = function (event) {
            console.log('onerror', event);

            if (event.error.match(/no-speech|audio-capture|not-allowed/)) {
                ignoreEndProcess = true;
            }

            micBtn.classList.add();
        };


        function start() {
            if (isRecognizing) {
                recognition.stop();
                console.log('stopped');
                return;
            }
            recognition.lang = language;
            recognition.start();
            ignoreEndProcess = false;

            finalTranscript = '';
            final_span.innerHTML = '';
            interim_span.innerHTML = '';
        }
        function delBtnHandler() {
            final_span.innerHTML = '';
        }

        function resultWordHandler(event) {
            console.log('clicked id : ' + event.target.value);
        }

        function final_span_Handler() {
            if (final_span.innerHTML) {
                const final_span_text = final_span.innerHTML;
                const final_arr = final_span_text.split(' ');

                let htmlEl = null;
                final_arr.forEach((value, index) => {
                    if (index === 0) {
                        htmlEl = `<span class="resultWord" id=0>` + value + '<span/>';
                    } else {
                        htmlEl = htmlEl + `<span class="resultWord" id=${index}>${value}<span/> `
                    }
                });
                console.log('htmlEl : ' + htmlEl);

                final_span.innerHTML = htmlEl;
            } else {
                return null;
            }
        }
        function initialize() {
            micBtn.addEventListener('click', start);
        }

        initialize();
    }
</script>
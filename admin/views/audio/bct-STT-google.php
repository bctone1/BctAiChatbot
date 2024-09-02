<?php
// WER 계산 함수
function wer($ref, $hyp) {
    $d = [];
    $ref_words = explode(' ', $ref);
    $hyp_words = explode(' ', $hyp);
    $n = count($ref_words);
    $m = count($hyp_words);
    for ($i = 0; $i <= $n; $i++) {
        $d[$i] = [];
        $d[$i][0] = $i;
    }
    for ($j = 0; $j <= $m; $j++) {
        $d[0][$j] = $j;
    }
    for ($i = 1; $i <= $n; $i++) {
        for ($j = 1; $j <= $m; $j++) {
            $cost = ($ref_words[$i - 1] == $hyp_words[$j - 1]) ? 0 : 1;
            $d[$i][$j] = min(
                $d[$i - 1][$j] + 1,
                $d[$i][$j - 1] + 1,
                $d[$i - 1][$j - 1] + $cost
            );
        }
    }
    return $d[$n][$m] / $n;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 입력 데이터 가져오기
    $reference_text = $_POST['reference_text'];
    $stt_text = $_POST['stt_text'];

    // TTS 오디오 파일 업로드 처리
    // if (isset($_FILES['tts_audio']) && $_FILES['tts_audio']['error'] == UPLOAD_ERR_OK) {
    //     $upload_dir = wp_upload_dir();
    //     $upload_file = $upload_dir['path'] . '/' . basename($_FILES['tts_audio']['name']);
    //     if (move_uploaded_file($_FILES['tts_audio']['tmp_name'], $upload_file)) {
    //         // 파일 업로드 성공
    //         $tts_audio_url = $upload_dir['url'] . '/' . basename($_FILES['tts_audio']['name']);
    //     } else {
    //         echo "파일 업로드 실패!";
    //         exit;
    //     }
    // } else {
    //     echo "파일 업로드 중 오류 발생!";
    //     exit;
    // }

    // WER 계산
    $wer_score = wer($reference_text, $stt_text);

    // 결과 표시
    echo "<div class='wrap'>";
    echo "<h2>TTS 평가 결과</h2>";
    echo "<p><strong>WER (Word Error Rate):</strong> " . number_format($wer_score * 100, 2) . "%</p>";
    echo "<p><strong>Reference Text:</strong> " . htmlspecialchars($reference_text) . "</p>";
    echo "<p><strong>STT Converted Text:</strong> " . htmlspecialchars($stt_text) . "</p>";
    // echo "<p><strong>TTS Audio File:</strong> <a href='" . esc_url($tts_audio_url) . "' target='_blank'>여기에서 확인</a></p>";
    echo "</div>";
}
?>

<h2 class="sectionTitle">STT Evaluation</h2>
    


    <form id="tts-evaluation-form" method="post" enctype="multipart/form-data">
        <table class="form-table">
            <tr valign="top">
                

                <th scope="row"><div class="formTitle"><strong>Reference Text</strong></div></th>
                
                <td><textarea name="reference_text" required></textarea></td>
            </tr>
            <tr valign="top">
                <th scope="row"><div class="formTitle"><strong>STT Audio File</strong></div></th>
                <td>
                    <div class="contentBox">
                        <input type="file" id="tts_audio" name="tts_audio"/>
                        <button type="button" id="audio-converter-button">audio converter</button>
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><div class="formTitle"><strong>STT speach</strong></div></th>
                <td>
                    

                    <button class="button-hero btn-start-record">
                        <span><i class="fa-solid fa-microphone"></i> <?php echo esc_html__('Speak', 'bctai') ?></span>
                    </button>

                    <button class="button-hero btn-pause-record" style="display: none;">
                        <span><i class="fa-solid fa-pause"></i> <?php echo esc_html__('Pause', 'bctai') ?></span>
                    </button>

                    <button class="button-hero btn-stop-record" style="display: none;">
                        <span><i class="fa-solid fa-stop"></i> <?php echo esc_html__('Stop', 'bctai') ?></span>
                    </button>
                    
                    <button class="button-hero btn-abort-record" style="display: none;">
                        <span><i class="fa-solid fa-xmark"></i> <?php echo esc_html__('Cancel', 'bctai') ?></span>
                    </button>
                </td>
            </tr>


            <tr valign="top">
                <th scope="row"><div class="formTitle"><strong>STT Converted Text</strong></div></th>
                <td><textarea name="stt_text" required class="stt_text"></textarea></td>
            </tr>
        </table>
        
        <button class="btn btnL bgPrimary" type="submit" name="bctai_submit" style="width:100%;">Evaluate TTS</button>
        
    </form>











<script>
    jQuery(document).ready(function ($) {
        $('#audio-converter-button').on('click', function (e) {
            alert("변환요청");
            e.preventDefault();
            
            let bctaiSpeechMessage = $('.stt_text');



            var formData = new FormData();
            var fileInput = $('#tts_audio')[0];
            var blob = fileInput.files[0]; 
            var fileName = fileInput.files[0].name;

            if (fileInput.files.length > 0) {
                //formData.append('blob', fileInput.files[0]);
                formData.append('audio', blob, fileName);
            }
            console.log(fileInput.files[0]);

            formData.append('action', 'bctai_speech_record');
            formData.append('nonce', '<?php echo wp_create_nonce('bctai-ajax-nonce') ?>');
            

            $.ajax({
                url: ajaxurl, // WordPress's AJAX handler URL
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response);
                    bctaiSpeechMessage.html(response.text);

                    // Handle the response from the server
                    // e.g., update the form with converted text or show a message
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error: ' + textStatus, errorThrown);
                }
            });
        });
    });
</script>




<script>
    jQuery(document).ready(function ($) {
        let bctaiBtnRecord = $('.btn-start-record');
        let bctaiPauseRecord = $('.btn-pause-record');
        let bctaiStopRecord = $('.btn-stop-record');
        let bctaiAbortRecord = $('.btn-abort-record');
        let bctaiSpeechMessage = $('.stt_text');


        let bctaiSpeechStream;
        let bctaiSpeechRec;
        let speechinput;
        let bctaiSpeechAudioContext = window.AudioContext || window.webkitAudioContext;
        let SpeechaudioContext;
        let bctaiSpeechAjaxRequest;
        function bctaiLoading(btn) {
            btn.attr('disabled', 'disabled');
            if (!btn.find('spinner').length) {
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility', 'unset');
        }
        function bctaiRmLoading(btn) {
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }
        function bctaispeechstartRecording() {
            var constraints = { audio: true, video: false }
            navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
                SpeechaudioContext = new bctaiSpeechAudioContext();
                bctaiSpeechStream = stream;
                speechinput = SpeechaudioContext.createMediaStreamSource(stream);
                bctaiSpeechRec = new Recorder(speechinput, { numChannels: 1 });
                bctaiSpeechRec.record();
            })
        }

        function bctaispeechpauseRecording() {
            if (bctaiSpeechRec.recording) {
                bctaiSpeechRec.stop();
            }
            else {
                bctaiSpeechRec.record()
            }
        }
        function bctaiSpeechAbortRecording() {
            bctaiSpeechRec.stop();
            bctaiSpeechStream.getAudioTracks()[0].stop();
        }

        function bctaispeechstopRecording() {
            //alert('hello');
            bctaiSpeechRec.stop();
            bctaiSpeechStream.getAudioTracks()[0].stop();
            bctaiSpeechRec.exportWAV(function (blob) {
                let url = URL.createObjectURL(blob);
                let reader = new FileReader();
                reader.onload = function (e) {
                    let audio = document.createElement('audio');
                    audio.src = e.target.result;
                    audio.addEventListener('loadedmetadata', function () {
                        let duration = audio.duration;
                    })
                }
                reader.readAsDataURL(blob);
                
                let data = new FormData();
                data.append('action', 'bctai_speech_record');
                data.append('audio', blob, 'speech_record.wav');
                data.append('nonce', '<?php echo wp_create_nonce('bctai-ajax-nonce') ?>');
                bctaiSpeechAjaxRequest = $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php') ?>',
                    data: data,
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (res) {
                        alert(JSON.stringify(res));
                        console.log(JSON.stringify(res));
                        
                        bctaiBtnRecord.css('display', 'inline-block');
                        if (res.status === 'success') {

                            bctaiSpeechMessage.html(res.text);

                        }
                        else {
                            alert(res.msg);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Error: ' + textStatus, errorThrown);
                    }
                })
            });
        }


        bctaiAbortRecord.click(function () {
            bctaiBtnRecord.css('display', 'inline-block');
            bctaiPauseRecord.hide();
            bctaiStopRecord.hide();
            bctaiAbortRecord.hide();
            bctaiSpeechMessage.empty();
            bctaiSpeechAbortRecording();
        })
        bctaiBtnRecord.click(function () {
            //alert('hi');
            
            bctaiBtnRecord.hide();
            bctaiSpeechMessage.empty();
            bctaiPauseRecord.css('display', 'inline-block');
            bctaiStopRecord.css('display', 'inline-block');
            bctaiAbortRecord.css('display', 'inline-block');
            bctaispeechstartRecording();
        });
        bctaiPauseRecord.click(function () {
            if (bctaiPauseRecord.hasClass('bctai-paused')) {
                bctaiPauseRecord.html('<span><i class="fa-solid fa-pause"></i> <?php echo esc_html__('Pause', 'bctai') ?></span>')
                bctaiPauseRecord.removeClass('bctai-paused');
            }
            else {
                bctaiPauseRecord.html('<span><i class="fa-solid fa-play"></i> <?php echo esc_html__('Continue', 'bctai') ?></span>')
                bctaiPauseRecord.addClass('bctai-paused');
            }
            bctaispeechpauseRecording();
        });
        bctaiStopRecord.click(function () {
            bctaiPauseRecord.hide();
            bctaiStopRecord.hide();
            bctaiAbortRecord.hide();
            bctaiBtnRecord.show();

            
            bctaispeechstopRecording();
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
    })
</script>



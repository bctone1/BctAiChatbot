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

<div class="wrap">
    <h2>TTS Evaluation</h2>
    <form method="post" enctype="multipart/form-data">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Reference Text</th>
                <td><textarea name="reference_text" required></textarea></td>
            </tr>

            <tr valign="top">
                <th scope="row">STT Converted Text</th>
                <td>
                    <textarea name="stt_text" required id="final_span"></textarea>
                    <span class="interim" id="interim_span"></span>
                </td>
            </tr>
        </table>

        <button type="button" class="mic btn btnL bgDarkGray"><?php echo __('마이크 활성화', 'bctai') ?>
            <span id="recording-state"></span>
        </button>

        <button class="btn btnL bgPrimary" type="submit" name="bctai_submit" style="width:100%;margin-top:20px;">Evaluate TTS</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var finalSpan = document.getElementById('final_span');
        var interimSpan = document.getElementById('interim_span');
        var micButton = document.querySelector('.mic');
        var recordingState = document.getElementById('recording-state');
        var recognizing = false;
        var finalTranscript = '';

        if (!('webkitSpeechRecognition' in window)) {
            alert('This browser does not support speech recognition.');
        } else {
            var recognition = new webkitSpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;

            recognition.onstart = function() {
                recognizing = true;
                recordingState.textContent = ' (활성화 됨)';
                micButton.disabled = true;
            };

            recognition.onerror = function(event) {
                console.error(event.error);
            };

            recognition.onend = function() {
                recognizing = false;
                recordingState.textContent = '';
                micButton.disabled = false;
            };

            recognition.onresult = function(event) {
                interimSpan.innerHTML = '';
                for (var i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript;
                    } else {
                        interimSpan.innerHTML += event.results[i][0].transcript;
                    }
                }
                finalSpan.value = finalTranscript;
            };

            micButton.addEventListener('click', function(event) {
                if (recognizing) {
                    recognition.stop();
                    return;
                }
                finalTranscript = '';
                recognition.start();
                recordingState.textContent = ' (활성화 중...)';
            });
        }
    });
</script>

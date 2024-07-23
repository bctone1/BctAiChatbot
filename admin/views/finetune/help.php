<?php
if (!defined('ABSPATH'))
    exit;

//$file_name='F:\test.pdf';
//$filetype = wp_check_filetype($file_name);
//echo $filetype['ext']; // will output jpg
//echo '<br />';
?>
<h1 style="font-weight: bolder;font-size: 30px;    margin-bottom: 20px;">
    <?php echo __('Preparation', 'kkkk') ?>
</h1> <br>

<h1 class="wp-heading-inline">AI 파인튜닝 방법</h1>

<p>파인튜닝 과정에 3가지 단계가 필요합니다.</p>

<ol>
    <li>
        <a href="<?php echo admin_url('admin.php?page=AI+Training&action=upload') ?>">Upload</a> - *.jsonl 포맷으로 된
        데이터셋을 업로드 합니다..
    </li>

</ol>

<h1 class="wp-heading-inline">1. *.jsonl 포맷 파일 업로드</h1>

<ol>
    <li>
        Upload 탭 이동
    </li>
    <li>
        100M 미만의 <code>*.jsonl</code> 포맷의 데이터셋을 업로드
    </li>
    <li>
        <p><code>*.jsonl</code> 포맷 샘플:</p>
        <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/example_jsonl.png" width="75%" />
    </li>

    <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/upload.png" width="50%" />

    <li>
        Datasets 탭에서 확인: <br /><br />

        <b>File</b>: Click on the "Choose File" button to select the file you wish to upload. OpenAI only accepts
        <code>*.jsonl</code> files and the maximum upload size is <b>100MB</b> per file. Learn about file format <a
            href="https://jsonlines.org/" target="_blank">here</a>. <br />
        <b>Purpose</b>: Select the purpose. Currently there is only the option which is "Fine-tune". <br />
        <b>Model Base</b>: Select the model base you wish to fine-tune your model. Options are: ada, babbage, curie and
        davinci. <br />
        <b>Custom Model Name</b>: Enter the custom model name for the fine-tuned model. This is optional. If you leave
        it blank, the fine-tuned model will be named after the model base you selected. <br />
    </li>
</ol>

<br />
<hr>

<h1 class="wp-heading-inline">Fine-Tunes 생성하는 방법</h1>

<ol>
    <li>
        To create a fine-tune request, click on the "Create Fine-Tune" button in the Dataset tab. This will create a
        fine-tune request on the OpenAI API based on the uploaded dataset.
    </li>
    <li>
        There is an important step here before creating your fine-tune request. You need to either create a new model or
        select an existing model from the dropdown list. If you select an existing mode, the fine-tuned model will be
        created based on the selected model. If you create a new model, the fine-tuned model will be created base you
        selected when you uploaded the dataset.</li>
    </li>
    <p>Here is an example of a fine-tune request:</p>
    <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/finetune1.png" width="25%" />
    <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/finetune2.png" width="25%" />

    <p>
        왜 중요한가? Because you can create multiple fine-tune requests for the same dataset. <br />
        예를 들어, you can create a fine-tune request for the same dataset using different model bases. Or you can create a
        fine-tune request for the same dataset using different models. This way, you can compare the results and choose
        the best model for your use case.
    </p>

    <p>
        A possible scenario is that you have huge dataset that is not possible to upload to OpenAI because of the 100MB
        limit. 이 경우, you can split your dataset into multiple files and upload them to OpenAI. <br />
        Then, you can create a fine-tune request for each file using the same model base. This way, you can create a
        fine-tuned model based on the same model base but with different datasets. </p>
    </p>

    <li>
        If you wish to upload a file for the same model, you will need to select the model from the dropdown list when
        you hit the "Create Fine-Tune" button.
    </li>
    <li>
        If you did not receive any error messages up to this point, congratulations, you have succeeded! Now, let's move
        on to viewing the fine-tune requests.
    </li>
</ol>

<br />
<hr>

<h1 class="wp-heading-inline">Fine-Tune 상태 확인</h1>

<li>
    To view the fine-tune requests, click on the "View Fine-Tunes" button. This will display all the fine-tune requests
    you have created.
</li>
<li>
    Here is an example of a fine-tune requests: <br />
    <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/finetune3.png" width="75%" />
</li>
<li>
    You can view information such as the fine-tune request's ID, creation date, model, and status. <br />
    There is also an "Training" column where you can perform various actions on the fine-tune requests, such as viewing
    the fine-tune request's details, viewing the fine-tuned model, and deleting the fine-tune request.
</li>
<li>
    There are 4 buttons in the "Training" column: Events, Hyper-params, Result files and Training files.

    <ol>
        <li>
            <b>Events</b>: This button will display the fine-tune request's events. You can view information such as the
            event's ID, creation date, ans status.
            It's important to note that fine-tuning a model can take some time, depending on the size of the dataset and
            the complexity of the model.
            <p>Here is an example of a fine-tune request's events:</p>
            <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/events2.png" width="75%" />
            <p>If the last message says "Fine-tuning succeeded", then the fine-tune request is complete and your model
                is ready to be used.</p>
            <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/events.png" width="75%" />
        </li>
        <li>
            <b>Hyper-params</b>: This button will display the fine-tune request's hyper-parameters. You can view
            information such as Epochs, batch size, Learning rate, and prompt loss weight.
            <p>Here is an example of a fine-tune request's hyper-parameters:</p>
            <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/hyper-params.png" width="75%" />
        </li>
        <li>
            <b>Result files</b>: This button will display the fine-tune request's result files. You can download the
            result file from training the model.
            <p>Here is an example of a fine-tune request's result files:</p>
            <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/result-files.png" width="75%" />
            <p> And here is how a result file looks like:</p>
            <img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/result-files-csv.png" width="75%" />
        </li>
        <li>
            <b>Training files</b>: This button will display the fine-tune request's training files. It is basically the
            file you upload to OpenAI.
        </li>
        <p>If you made it this far, congratulations, you have succeeded! Now, let's move on to viewing the fine-tuned
            models.</p>
    </ol>
</li>

<br />
<hr>

<h1 class="wp-heading-inline">Fine-Tuned 모델 사용하기</h1>

<li>
    Let say you already have a fine-tune request that is complete. Now, you want to use the fine-tuned model to with
    ChatBox in your webiste. To do that, please proceed to the plugins settings page and click ChatGPT tab.
</li>

<p>Here is an example of the ChatGPT tab:</p>
<img src="<?php echo esc_html(BCTAI_PLUGIN_URL) ?>admin/images/models.png" width="75%" />


<li>
    You will see that your fine-tuned model is now available in the dropdown list. You can select it and click on the
    "Save Changes" button.
    This means from now on your ChatBox will use the fine-tuned model you selected.
</li>
<li>If you don't see your fine-tuned model in the dropdown list, please make sure that the fine-tune request is
    complete. You can also click on "Sync Models" link to get latest models.</li>
<li>Now head over to the ChatGPT ans ask your ChatBox a question. You should see that the ChatBox is now using the
    fine-tuned model.</li>
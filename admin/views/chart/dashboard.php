<?php
if (!defined('ABSPATH'))
    exit;
global $wpdb;




if (isset($_GET['start_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}else{
    $start_date = date('Y-m-d', strtotime('-6 days'));
    $end_date = date('Y-m-d');
}




if (isset($_GET['new_value']) && $_GET['new_value'] == 7) {
    // $count_day = $_GET['new_value'];
    $start_date = date('Y-m-d', strtotime('-6 days'));
    $end_date = date('Y-m-d');
}else if(isset($_GET['new_value']) && $_GET['new_value'] == 14) {
    // $count_day = $_GET['new_value'];
    $start_date = date('Y-m-d', strtotime('-13 days'));
    $end_date = date('Y-m-d');
}else if(isset($_GET['new_value']) && $_GET['new_value'] == 30) {
    // $count_day = $_GET['new_value'];
    $start_date = date('Y-m-d', strtotime('-29 days'));
    $end_date = date('Y-m-d');
}else{
    // $count_day = 7;
    // echo $count_day;
}







//챗봇 데이터 쿼리
$query = $wpdb->prepare("
SELECT *, DATE_FORMAT(FROM_UNIXTIME(created_at), '%%Y.%%m.%%d') AS formatted_created_at
FROM (
    SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS date_range
    FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) c
) date_ranges
LEFT JOIN " . $wpdb->prefix . "bctai_chatlogs ON DATE_FORMAT(FROM_UNIXTIME(created_at), '%%Y-%%m-%%d') = date_ranges.date_range
WHERE date_ranges.date_range BETWEEN '%s' AND '%s'
ORDER BY date_ranges.date_range DESC, created_at DESC;
", $start_date,$end_date);


//echo $query;

//받아온 데이터를 저장하는 변수
$bctai_logs = $wpdb->get_results($query);
// echo '<pre>'; print_r($bctai_logs); echo '</pre>';

$date_ranges = array(); //쿼리결과 date_ranges의 출력값을 담을 변수배열 준비
$tokens_total = array();


foreach ($bctai_logs as $bctai_log) {
    $all_messages = json_decode($bctai_log->data, true);
    $tokens = 0;
    if ($bctai_log->source != 'shortcode') {
        foreach ($all_messages as $item) {
            if (isset($item['token']) && !empty($item['token'])) {
                $tokens += $item['token'];
            }
        }
    }
    //배열의 담는 코드
    $date_ranges[] = $bctai_log->date_range;
    $tokens_total[] = $tokens;
}

//쿼리를 기반으로 출력된 데이터를 날짜별로 그룹화
foreach ($date_ranges as $index => $date) {
    $grouped_tokens[$date][] = $tokens_total[$index];
}
// echo '<pre>';print_r($grouped_tokens); echo '<pre>';

//그룹화 된 배열을 계산하여 넣을 또 다른 배열 준비
$dates_array = array();
$summed_tokens = array();
$total_charge = array();

//같은 날짜의 토큰 및 날짜를 하나로 합산
foreach ($grouped_tokens as $date => $widget_token) {
    $dates_array[] = $date;
    $summed_tokens[] = array_sum($widget_token);
    $total_charge[] = array_sum($widget_token) * 0.000002;
}



// wp_option 테스트
// $option_prefix = 'bctai';
// $site_options = $wpdb->get_results( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '{$option_prefix}%'" );

// foreach ( $site_options as $option ) {
//     echo '<pre>';print_r($option->option_name); echo '<pre>';
// }








//임베딩 토큰 시작
$_embedding_query = $wpdb->prepare("
SELECT DISTINCT dates.date, wp.*
FROM (
    SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
    FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
    CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
) dates
LEFT JOIN " . $wpdb->prefix . "posts wp ON DATE(wp.post_date) = dates.date AND wp.post_type = 'bctai_builder'
WHERE dates.date BETWEEN '%s' AND '%s'
", $start_date,$end_date);
//echo '<pre>';print_r($_embedding_query);echo '<pre>';

$bctai_embedding_logs = $wpdb->get_results($_embedding_query);

$embed_logs_ID = array();
$embed_logs_Date = array();

foreach ($bctai_embedding_logs as $bctai_embedding_log) {
    $embed_logs_ID[] = $bctai_embedding_log->ID;
    $embed_logs_Date[] = $bctai_embedding_log->DATE;
}

foreach ($embed_logs_Date as $index => $date) {
    $grouped[$date][] = $embed_logs_ID[$index];
}

$embedding_days = array();
$embedding_group_token = array();

foreach ($grouped as $date => $embedding_tokens_ID) {
    $embedding_days[] = $date;
    $totalSum = 0;
    foreach ($embedding_tokens_ID as $embedding_token_id) {
        $postID = $embedding_token_id;

        $embedding_token_sum_sql = $wpdb->prepare("
        SELECT meta_value
        FROM " . $wpdb->prefix . "postmeta
        WHERE post_id= %d
        AND meta_key = 'bctai_embedding_token';
        ", $postID);

        $result = $wpdb->get_var($embedding_token_sum_sql);

        if (is_numeric($result)) {
            $totalSum += $result;
        }
    }
    $embedding_group_token[] = $totalSum;
}


#TTS 사용량
$audio_query = $wpdb->prepare("
WITH RECURSIVE DateSeries AS (
    SELECT '%s' AS log_date
    UNION ALL
    SELECT DATE_ADD(log_date, INTERVAL 1 DAY)
    FROM DateSeries
    WHERE log_date < '%s'
)
SELECT ds.log_date, COALESCE(SUM(bal.size), 0) AS total_size
FROM DateSeries ds
LEFT JOIN " . $wpdb->prefix . "bctai_audio_logs bal ON DATE(bal.created_at) = ds.log_date
GROUP BY ds.log_date
ORDER BY ds.log_date DESC;
", $start_date,$end_date);
// echo $audio_query;

$audio_logs = $wpdb->get_results($audio_query);
// echo '<pre>';print_r($audio_logs);echo '<pre>';

$audio_date_count = array();
$summed_size = array();

foreach ($audio_logs as $log) {
    $audio_date_count[] = $log->log_date;
    $summed_size[] = $log->total_size;
}

#STT 사용량
$STT_query = $wpdb->prepare("
WITH RECURSIVE DateSeries AS (
    SELECT '%s' AS log_date
    UNION ALL
    SELECT DATE_ADD(log_date, INTERVAL 1 DAY)
    FROM DateSeries
    WHERE log_date < '%s'
)
SELECT ds.log_date, COALESCE(SUM(bal.size), 0) AS total_size
FROM DateSeries ds
LEFT JOIN " . $wpdb->prefix . "bctai_STT_logs bal ON DATE(bal.created_at) = ds.log_date
GROUP BY ds.log_date
ORDER BY ds.log_date DESC;
", $start_date,$end_date);


$STT_logs = $wpdb->get_results($STT_query);


$STT_date_count = array();
$STT_summed_size = array();

foreach ($STT_logs as $log) {
    $STT_date_count[] = $log->log_date;
    $STT_summed_size[] = $log->total_size;
}








// echo '<pre>';print_r($audio_date_count);echo '<pre>';


//총 방문자
$total_count = $wpdb->get_var("
SELECT COUNT(DISTINCT SESSION) AS unique_visitors
FROM " . $wpdb->prefix . "bctai_visitor_count;");

//오늘 방문자
$total_count_today = $wpdb->get_var("
SELECT COUNT(DISTINCT SESSION) AS unique_visitors
FROM " . $wpdb->prefix . "bctai_visitor_count
WHERE DATE(FROM_UNIXTIME(TIME)) = CURDATE();");


//총 페이지뷰
$total_page_count = $wpdb->get_var("
    SELECT COUNT(DISTINCT CASE WHEN session = page_url THEN session ELSE CONCAT(session, page_url) END) AS unique_visitors
    FROM " . $wpdb->prefix . "bctai_visitor_count;"
);
//오늘 페이지뷰
$total_page_count_today = $wpdb->get_var("
    SELECT COUNT(DISTINCT CASE WHEN session = page_url THEN session ELSE CONCAT(session, page_url) END) AS unique_visitors
    FROM " . $wpdb->prefix . "bctai_visitor_count
    WHERE DATE(FROM_UNIXTIME(TIME)) = CURDATE();"
);








//총 회원수
$user_count = $wpdb->get_var("
    SELECT COUNT(*) AS user_count
    FROM {$wpdb->users}
    WHERE user_status = 0
");
// 오늘 등록된 사용자 수 쿼리
$user_count_today = $wpdb->get_var("
    SELECT COUNT(*) AS user_count
    FROM {$wpdb->users}
    WHERE user_status = 0
    AND DATE(user_registered) = CURDATE()
");




//페이지 랭킹
$page_view_qurry = $wpdb->prepare("
SELECT page_url, COUNT(*) AS COUNT
FROM (
    SELECT page_url, SESSION, FROM_UNIXTIME(TIME, '%%Y-%%m-%%d') AS formatted_time
    FROM " . $wpdb->prefix . "bctai_visitor_count
    WHERE DATE(FROM_UNIXTIME(TIME)) BETWEEN '%s' AND '%s'
    GROUP BY page_url, SESSION, FROM_UNIXTIME(TIME, '%%Y-%%m-%%d')
) AS unique_data
GROUP BY page_url
ORDER BY COUNT DESC
LIMIT 10
", $start_date,$end_date);


$page_view_qurry_values = $wpdb->get_results($page_view_qurry);
//echo '<pre>'; print_r($page_view_qurry_values); echo '</pre>';

foreach($page_view_qurry_values as $value){
    $get_post_title = get_post($value->page_url);

    $page_title[] = $get_post_title->post_title;
    $page_views[] = $value->COUNT;
}








$user_view_query = $wpdb->prepare("
SELECT DATE_RANGE.date AS DATE,
       COUNT(DISTINCT " . $wpdb->prefix . "bctai_visitor_count.session) AS session_count
FROM (
    SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
    FROM (
        SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL
        SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
    ) AS a
    CROSS JOIN (
        SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL
        SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
    ) AS b
    CROSS JOIN (
        SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL
        SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
    ) AS c
) AS DATE_RANGE
LEFT JOIN " . $wpdb->prefix . "bctai_visitor_count ON DATE_RANGE.date = DATE(FROM_UNIXTIME(" . $wpdb->prefix . "bctai_visitor_count.time))
WHERE DATE_RANGE.date BETWEEN '%s' AND '%s'
GROUP BY DATE_RANGE.date
ORDER BY DATE_RANGE.date;
", $start_date,$end_date);

//echo $user_view_query;
$user_view_query_values = $wpdb->get_results($user_view_query);
//echo '<pre>'; print_r($user_view_query_values); echo '</pre>';

foreach($user_view_query_values as $value){
    $user_view_date[] = $value->DATE;
    $user_view_session[] = $value->session_count;
}
// echo '<pre>'; print_r($user_view_date); echo '</pre>';




?>



<div>
    <h1 style="font: normal normal 900 24px/35px Noto Sans KR; margin:0px;"><?php echo __('Dashboard', 'bctai') ?></h1>


    <div class="first_content">
        <div class="innerBox">
            <h2 style="font: normal normal bold 16px/22px Noto Sans KR;letter-spacing: -0.16px;color: #352F39;opacity: 1;"><?php echo __('User view','bctai') ?></h2>
            <div class="cnt">

                <div class="cnt_inner">
                    <h1><?php echo $total_count_today?></h1>
                    <p><?php echo __('Today','bctai')?></p>
                </div>
                <div class="cnt_inner2">
                    <h1><?php echo $total_count?></h1>
                    <p><?php echo __('Total','bctai')?></p>
                </div>
            </div>
        </div>

        <div class="innerBox">
            <h2 style="font: normal normal bold 16px/22px Noto Sans KR;letter-spacing: -0.16px;color: #352F39;opacity: 1;"><?php echo __('Page view','bctai') ?></h2>
            <div class="cnt">

                <div class="cnt_inner">
                    <h1><?php echo $total_page_count_today?></h1>
                    <p><?php echo __('Today','bctai')?></p>
                </div>
                <div class="cnt_inner2">
                    <h1><?php echo $total_page_count?></h1>
                    <p><?php echo __('Total','bctai')?></p>
                </div>
            </div>
        </div>

        <div class="innerBox">
            <h2 style="font: normal normal bold 16px/22px Noto Sans KR;letter-spacing: -0.16px;color: #352F39;opacity: 1;"><?php echo __('New member','bctai') ?></h2>
            <div class="cnt">
                <div class="cnt_inner">
                    <h1><?php echo $user_count_today?></h1>
                    <p><?php echo __('Today','bctai')?></p>
                </div>
                <div class="cnt_inner2">
                    <h1><?php echo $user_count?></h1>
                    <p><?php echo __('Total','bctai')?></p>
                </div>
            </div>
        </div>
    </div>


    

    

    
    

    
    <!--그래프 영역-->
    <div class="last_content" style="">

        

        <div class="second_content" >
        <!--기간설정 영역-->
            <div class="period">
                <input type="date" id="date-start">
                <span style="font: normal normal normal 14px/20px Noto Sans KR;letter-spacing: 0px;color: #352F39;opacity: 1;margin: 0px 10px;">~</span>
                <input type="date" id="date-end">

                <button type="button" class="btn-square-medium" onclick="changeRange(7)">7<?php echo __('Days','bctai'); ?></button>
                <button type="button" class="btn-square-medium" onclick="changeRange(14)">14<?php echo __('Days','bctai'); ?></button>
                <button type="button" class="btn-square-medium" onclick="changeRange(30)">30<?php echo __('Days','bctai'); ?></button>
            </div>
        </div>

        <div class="graf_box" style="margin-top: 40px;">
            <h2><?php echo __('User view','bctai')?></h2>
            <div id ="user_view"></div>
        </div>
        
        <div class="graf_box" style="margin-top: 40px;">
            <h2><?php echo __('Page rank','bctai')?></h2>
            <div id ="visitor"></div>
        </div>

        <div class="graf_box" style="margin-top: 40px;">
            <h2><?php echo __('Chatbot Usage', 'bctai') ?></h2> 
            <div id="chart"></div>
        </div>

        <div class="graf_box" style="margin-top: 40px;">
            <h2><?php echo __('Embedding Usage','bctai')?></h2>
            <div id="embedding_chart"></div>
        </div>

        <div class="graf_box" style="margin-top: 40px;">
            <h2><?php echo __('Google TTS Usage','bctai')?></h2>
            <div id="TTS_chart"></div>
        </div>

        <div class="graf_box" style="margin-top: 40px;">
            <h2><?php echo __('STT Usage','bctai')?></h2>
            <div id="STT_chart"></div>
        </div>

       
    </div>
</div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.11/c3.min.css"/>
<script src="https://d3js.org/d3.v3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/c3/0.4.11/c3.min.js"></script>


<script>

    var page_title = <?php echo wp_json_encode($page_title); ?>;
    var page_views = ['<?php echo __('page_views','bctai'); ?>', ...<?php echo wp_json_encode($page_views); ?>];

    var chart = c3.generate({
        bindto: '#visitor',
        size: { height: 300},
        data: {
            columns: [page_views],
            type: 'bar'
        },
        axis: {
            x: {
                type: 'category', 
                categories: page_title
            }
        },
        bar: {
            width: {
                ratio: 0.5
            }
        }
    });


    var user_view_date = <?php echo wp_json_encode($user_view_date); ?>;
    var user_view_session = ['<?php echo __('user_views','bctai'); ?>', ...<?php echo wp_json_encode($user_view_session); ?>];

    var chart = c3.generate({
        bindto: '#user_view',
        size: { height: 300},
        data: {
            columns: [user_view_session],
            type: 'bar',
            colors: {user_views: '#9961c0'}
        },
        axis: {
            x: {
                type: 'category', 
                categories: user_view_date
            }
        },
        bar: {
            width: {
                ratio: 0.5
            }
        }
    });

    



        
</script>


<script>
    // C3.js 위젯 챗봇사용량 차트 생성
    var widget_token = ['<?php echo __('Token','bctai'); ?>', ...<?php echo wp_json_encode($summed_tokens); ?>];
    var widget_charge = ['<?php echo __('Charge','bctai'); ?>', ...<?php echo wp_json_encode($total_charge); ?>];
    var dateArr = ['<?php echo __('Date','bctai'); ?>', ...<?php echo wp_json_encode($dates_array); ?>];

    var chart = c3.generate({
        bindto: '#chart',
        //size: { height: 300 },
        padding: { right: 30 },
        point: { r: 5 },
        data: {
            x: '<?php echo __('Date','bctai'); ?>',
            columns: [dateArr, widget_token, widget_charge],
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    // count: objCount,
                    format: '%Y-%m-%d',
                },
            },
            y: {
                min: 0,
                padding: { bottom: 10 },
            },
        },
        //legend: { show: false },
        grid: { y: { show: true } },
    });


    //임베딩 토큰 사용량
    var widget_token2 = ['<?php echo __('Token','bctai'); ?>', ...<?php echo wp_json_encode($embedding_group_token); ?>];
    var dateArr2 = ['<?php echo __('Date','bctai'); ?>', ...<?php echo wp_json_encode($embedding_days); ?>];
    var chart = c3.generate({
        bindto: '#embedding_chart',
        //size: { height: 300 },
        padding: { right: 30 },
        point: { r: 5 },
        data: {
            x: '<?php echo __('Date','bctai'); ?>',
            columns: [dateArr2, widget_token2],
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    // count: objCount,
                    format: '%Y-%m-%d',
                },
            },
            y: {
                min: 0,
                padding: { bottom: 10 },
            },
        },
        //legend: { show: false },
        grid: { y: { show: true } },
    });


    
    // C3.js TTS 사용량 차트 생성
    var TTS_date = ['<?php echo __('Date','bctai'); ?>', ...<?php echo wp_json_encode($audio_date_count); ?>];
    var TTS_size = ['<?php echo __('Size (KB)','bctai'); ?>', ...<?php echo wp_json_encode($summed_size); ?>];
    
    var chart = c3.generate({
        bindto: '#TTS_chart',
        //size: { height: 300 },
        padding: { right: 30 },
        point: { r: 5 },
        data: {
            x: '<?php echo __('Date','bctai'); ?>',
            columns: [TTS_date, TTS_size],
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    // count: objCount,
                    format: '%Y-%m-%d',
                },
            },
            y: {
                min: 0,
                padding: { bottom: 10 },
            },
        },
        //legend: { show: false },
        grid: { y: { show: true } },
    });

    //STT사용량
    var TTS_date = ['<?php echo __('Date','bctai'); ?>', ...<?php echo wp_json_encode($STT_date_count); ?>];
    var TTS_size = ['<?php echo __('Request','bctai'); ?>', ...<?php echo wp_json_encode($STT_summed_size); ?>];
    
    var chart = c3.generate({
        bindto: '#STT_chart',
        //size: { height: 300 },
        padding: { right: 30 },
        point: { r: 5 },
        data: {
            x: '<?php echo __('Date','bctai'); ?>',
            columns: [TTS_date, TTS_size],
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    // count: objCount,
                    format: '%Y-%m-%d',
                },
            },
            y: {
                min: 0,
                padding: { bottom: 10 },
            },
        },
        //legend: { show: false },
        grid: { y: { show: true } },
    });





    


    //날짜 설정

    
    var start_dateInput = document.getElementById("date-start");
    var end_dateInput = document.getElementById("date-end");




    // start_dateInput.value = <?php echo wp_json_encode(end($date_ranges))?>;
    // end_dateInput.value = new Date().toISOString().substring(0, 10);

    start_dateInput.value = "<?php echo $start_date?>";
    end_dateInput.value = "<?php echo $end_date?>";



    start_dateInput.addEventListener("change", function() {
        var selected_Start_Date = start_dateInput.value;
        var selected_End_Date = end_dateInput.value;

        // console.log("선택된 날짜:", selected_Start_Date + selected_End_Date);
        changeDate(selected_Start_Date,selected_End_Date);

    });
    end_dateInput.addEventListener("change", function() {
        var selected_Start_Date = start_dateInput.value;
        var selected_End_Date = end_dateInput.value;

        // console.log("선택된 날짜:", selected_Start_Date + selected_End_Date);
        changeDate(selected_Start_Date,selected_End_Date);

    });



    function changeDate(start_date,end_date){

        let home_url = '<?php echo home_url(); ?>';
        
        let newdate_url = home_url + "/wp-admin/admin.php?page=Statistics&start_date=" + encodeURIComponent(start_date) + "&end_date=" + encodeURIComponent(end_date);
        window.location.href = newdate_url;

        //alert(newdate_url);
    }



    function changeRange(count) {
        let home_url = '<?php echo home_url(); ?>';
        var newUrl = home_url+"/wp-admin/admin.php?page=Statistics&new_value=" + encodeURIComponent(count);
        window.location.href = newUrl;
    }
</script>
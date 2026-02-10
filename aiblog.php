<?php
/**
 * Plugin Name: AI 블로그 자동화 워커 연동 PRO
 * Description: 글쓰기 + 썸네일 + SEO + 예약발행 완전 자동화 (Cloudflare Workers)
 * Version: 1.0.0
 * Author: Auto AI System
 */

if (!defined('ABSPATH')) exit;

/* ===============================
 * 설정값
 * =============================== */
define('AI_WRITER_WORKER', 'https://blogwriterai.jiji15899.workers.dev/');
define('AI_THUMB_WORKER', 'https://blogthumbnailmake.jiji15899.workers.dev/');

/* ===============================
 * 관리자 메타박스
 * =============================== */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'ai_blog_worker',
        '🤖 AI 글쓰기 자동화',
        'ai_blog_worker_box',
        'post',
        'side',
        'high'
    );
});

function ai_blog_worker_box() {
?>
<p><strong>글 주제</strong></p>
<input type="text" id="ai_topic" style="width:100%" placeholder="예: 2026년 정부지원금">

<p><strong>글 생성기 유형</strong></p>
<select id="ai_type" style="width:100%">
    <option value="A">A형 – 정보형</option>
    <option value="B">B형 – 수익형</option>
    <option value="C">C형 – 후기형</option>
    <option value="D">D형 – 비교형</option>
    <option value="E">E형 – 가이드형</option>
</select>

<p><strong>예약 발행</strong></p>
<input type="datetime-local" id="ai_schedule" style="width:100%">

<button class="button button-primary" id="ai_generate" style="width:100%;margin-top:10px">
🚀 AI 자동 생성
</button>

<script>
document.getElementById('ai_generate').onclick = async () => {
    const topic = ai_topic.value.trim();
    if (!topic) return alert('주제 입력');

    const res = await fetch(ajaxurl, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'ai_generate_post',
            topic: topic,
            type: ai_type.value,
            schedule: ai_schedule.value
        })
    });

    const r = await res.json();
    alert(r.success ? '완료' : r.data);
};
</script>
<?php
}

/* ===============================
 * AJAX 처리
 * =============================== */
add_action('wp_ajax_ai_generate_post', function () {

    $topic    = sanitize_text_field($_POST['topic']);
    $type     = sanitize_text_field($_POST['type']);
    $schedule = sanitize_text_field($_POST['schedule']);

    /* ---- 1. 글쓰기 워커 ---- */
    $writer = wp_remote_post(AI_WRITER_WORKER, [
        'headers' => ['Content-Type'=>'application/json'],
        'body' => json_encode([
            'topic' => $topic,
            'type'  => $type
        ]),
        'timeout' => 60
    ]);

    if (is_wp_error($writer)) {
        wp_send_json_error('글쓰기 워커 오류');
    }

    $writer_body = json_decode(wp_remote_retrieve_body($writer), true);

    $content     = $writer_body['content'];
    $focus       = $writer_body['focus_keyword'];
    $meta_desc   = $writer_body['meta_description'];
    $category    = $writer_body['category'];
    $internal    = $writer_body['internal_links'];

    /* ---- 2. 썸네일 워커 ---- */
    $thumb = wp_remote_post(AI_THUMB_WORKER, [
        'headers' => ['Content-Type'=>'application/json'],
        'body' => json_encode([
            'topic' => $topic
        ])
    ]);

    $thumb_url = '';
    if (!is_wp_error($thumb)) {
        $thumb_body = json_decode(wp_remote_retrieve_body($thumb), true);
        $thumb_url  = $thumb_body['image'];
    }

    /* ---- 3. 포스트 생성 ---- */
    $post_data = [
        'post_title'   => $topic,
        'post_content' => $content . "\n\n" . implode("\n", $internal),
        'post_status'  => $schedule ? 'future' : 'draft',
        'post_date'    => $schedule ?: current_time('mysql')
    ];

    $post_id = wp_insert_post($post_data);

    /* ---- 4. 카테고리 ---- */
    if ($category) {
        wp_set_post_terms($post_id, [$category], 'category');
    }

    /* ---- 5. RankMath SEO ---- */
    update_post_meta($post_id, 'rank_math_focus_keyword', $focus);
    update_post_meta($post_id, 'rank_math_description', $meta_desc);

    /* ---- 6. URL 슬러그 ---- */
    wp_update_post([
        'ID' => $post_id,
        'post_name' => sanitize_title($focus)
    ]);

    /* ---- 7. 썸네일 첨부 ---- */
    if ($thumb_url) {
        require_once ABSPATH.'wp-admin/includes/media.php';
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/image.php';

        $thumb_id = media_sideload_image($thumb_url, $post_id, null, 'id');
        if (!is_wp_error($thumb_id)) {
            set_post_thumbnail($post_id, $thumb_id);
        }
    }

    wp_send_json_success('OK');
});

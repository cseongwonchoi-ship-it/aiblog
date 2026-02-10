<?php
/**
 * Plugin Name: AI 블로그 자동화 (Worker 완성판)
 */

if (!defined('ABSPATH')) exit;

define('WRITER', 'https://blogwriterai.jiji15899.workers.dev/');
define('REWRITE', 'https://dragrewriteai.jiji15899.workers.dev/');
define('THUMB', 'https://blogthumbnailmake.jiji15899.workers.dev/');

/* ===============================
 * 같은 카테고리 내부링크 자동 추출
 * =============================== */
function ai_get_internal_links($category_id, $exclude_id = 0, $limit = 3) {
    $posts = get_posts([
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'post__not_in' => [$exclude_id],
        'category__in' => [$category_id]
    ]);

    $links = [];
    foreach ($posts as $p) {
        $links[] = '<a href="'.get_permalink($p->ID).'">'.$p->post_title.'</a>';
    }
    return $links;
}

/* ===============================
 * 글 생성
 * =============================== */
add_action('wp_ajax_ai_generate', function () {

    $topic = sanitize_text_field($_POST['topic']);
    $type  = sanitize_text_field($_POST['type']);

    $res = wp_remote_post(WRITER, [
        'headers' => ['Content-Type'=>'application/json'],
        'body' => json_encode(compact('topic','type')),
        'timeout' => 60
    ]);

    if (is_wp_error($res)) {
        wp_send_json_error('글쓰기 워커 오류');
    }

    $data = json_decode(wp_remote_retrieve_body($res), true);

    if (empty($data['content'])) {
        wp_send_json_error('빈 콘텐츠');
    }

    /* 카테고리 생성 또는 매칭 */
    $cat = term_exists($data['category'], 'category');
    if (!$cat) {
        $cat = wp_insert_term($data['category'], 'category');
    }
    $cat_id = is_array($cat) ? $cat['term_id'] : $cat;

    /* 내부링크 (같은 카테고리만) */
    $internal_links = ai_get_internal_links($cat_id);

    $content = $data['content'];
    if ($internal_links) {
        $content .= '<h3>함께 보면 좋은 글</h3><ul><li>' .
                    implode('</li><li>', $internal_links) .
                    '</li></ul>';
    }

    $post_id = wp_insert_post([
        'post_title'   => $topic,
        'post_content' => $content,
        'post_status'  => 'draft'
    ]);

    wp_set_post_terms($post_id, [$cat_id], 'category');

    update_post_meta($post_id, 'rank_math_focus_keyword', $data['focus_keyword']);
    update_post_meta($post_id, 'rank_math_description', $data['meta_description']);

    wp_send_json_success($post_id);
});

/* ===============================
 * 드래그 문장 AI 재작성
 * =============================== */
add_action('wp_ajax_ai_rewrite', function () {

    $text = wp_kses_post($_POST['text']);

    $res = wp_remote_post(REWRITE, [
        'headers' => ['Content-Type'=>'application/json'],
        'body' => json_encode(['text' => $text]),
        'timeout' => 30
    ]);

    if (is_wp_error($res)) {
        wp_send_json_error('재작성 워커 오류');
    }

    $data = json_decode(wp_remote_retrieve_body($res), true);

    if (empty($data['rewritten'])) {
        wp_send_json_error('재작성 실패');
    }

    wp_send_json_success($data['rewritten']);
});

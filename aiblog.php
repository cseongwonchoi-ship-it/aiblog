<?php
/**
 * Plugin Name: 아백 AI 마스터 엔진 Pro
 * Description: 애드센스 승인율 100% 보장 AI 시스템 (Groq AI 통합)
 * Version: 3.0
 * Author: Abaek Team
 */

if (!defined('ABSPATH')) exit;

class Abaek_AI_Master_V3 {
    
    private $groq_api_key = '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';
    
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('admin_head', [$this, 'inline_styles']);
        add_action('admin_footer', [$this, 'inline_scripts']);
        add_action('wp_ajax_abaek_generate', [$this, 'ajax_generate']);
        add_action('wp_ajax_abaek_thumbnail', [$this, 'ajax_thumbnail']);
    }
    
    public function add_metabox() {
        add_meta_box(
            'abaek_ai_box',
            '🎯 아백 AI 마스터',
            [$this, 'render_box'],
            ['post', 'page'],
            'side',
            'high'
        );
    }
    
    public function render_box() {
        $nonce = wp_create_nonce('abaek_nonce');
        $post_id = get_the_ID();
        ?>
        <div id="abaek-container">
            <div class="abaek-tabs">
                <button class="tab-btn active" data-tab="content">✍️ 콘텐츠</button>
                <button class="tab-btn" data-tab="thumb">🖼️ 썸네일</button>
            </div>
            
            <div class="tab-content active" id="content-tab">
                <div class="input-group">
                    <label>🎯 주제 / 키워드</label>
                    <input type="text" id="topic" placeholder="예: 2026년 청년도약계좌 신청방법">
                </div>
                
                <div class="input-group">
                    <label>⚙️ 생성 모드</label>
                    <select id="mode">
                        <option value="adsense">💎 애드센스 승인용 (승인율 100%)</option>
                        <option value="subsidy">💰 지원금 글 생성기 (표/차트)</option>
                        <option value="pasona">🔥 파소나 수익형 (광고 최적화)</option>
                        <option value="seo">🚀 SEO 최적화 (검색 유입)</option>
                        <option value="ad_insert">💸 광고 삽입형 (수익 극대화)</option>
                    </select>
                </div>
                
                <div class="input-group" id="adcode-group" style="display:none;">
                    <label>📢 광고 코드 (여러 개 가능)</label>
                    <div class="ad-inputs">
                        <div class="ad-item">
                            <select class="ad-type">
                                <option value="">-- 광고 종류 --</option>
                                <option value="dable">데이블 (Dable)</option>
                                <option value="revcontent">레브콘텐츠 (RevContent)</option>
                                <option value="adsense">애드센스 (AdSense)</option>
                                <option value="coupang">쿠팡 파트너스</option>
                                <option value="mgid">MGID</option>
                                <option value="taboola">타불라 (Taboola)</option>
                                <option value="custom">기타 광고</option>
                            </select>
                            <textarea class="ad-code" rows="3" placeholder="광고 코드를 붙여넣으세요"></textarea>
                        </div>
                    </div>
                    <button type="button" class="btn-add-ad" id="add-ad-btn">+ 광고 코드 추가</button>
                    
                    <div class="input-group" style="margin-top: 10px;">
                        <label>📍 광고 삽입 위치</label>
                        <div class="ad-position-options">
                            <label class="checkbox-label">
                                <input type="checkbox" class="ad-position" value="top" checked> 
                                상단 (제목 아래)
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" class="ad-position" value="middle" checked> 
                                중간 (본문 중앙)
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" class="ad-position" value="bottom" checked> 
                                하단 (글 끝)
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" class="ad-position" value="between"> 
                                단락 사이 (자동 분산)
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="row-2">
                    <div class="input-group">
                        <label>언어</label>
                        <select id="lang">
                            <option value="ko">🇰🇷 한국어</option>
                            <option value="en">🇺🇸 English</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>글자 수</label>
                        <select id="length">
                            <option value="3000">3000자</option>
                            <option value="5000" selected>5000자</option>
                            <option value="8000">8000자</option>
                        </select>
                    </div>
                </div>
                
                <button class="btn-primary" id="generate-btn">
                    ✨ 마스터피스 생성
                </button>
                
                <button class="btn-quick" id="quick-btn">
                    ⚡ 긴급 작성 (10초)
                </button>
                
                <div id="progress" style="display:none;">
                    <div class="progress-text">AI 분석 중...</div>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="progress-percent">0%</div>
                </div>
                
                <div id="result" style="display:none;">
                    <div class="result-title">✅ 생성 완료!</div>
                    <div class="result-stats">
                        <div>SEO: <span id="seo-score">-</span></div>
                        <div>수익: <span id="rev-score">-</span></div>
                        <div>승인: <span id="app-score">-</span></div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="thumb-tab">
                <div class="input-group">
                    <label>🎨 썸네일 설명</label>
                    <textarea id="thumb-prompt" rows="3" placeholder="예: 청년도약계좌를 설명하는 밝은 이미지"></textarea>
                </div>
                
                <div class="input-group">
                    <label>스타일</label>
                    <select id="thumb-style">
                        <option value="professional">전문적</option>
                        <option value="colorful">화려함</option>
                        <option value="minimal">미니멀</option>
                        <option value="dramatic">드라마틱</option>
                    </select>
                </div>
                
                <button class="btn-primary" id="thumb-btn">
                    🎨 썸네일 생성 (300KB 이하)
                </button>
                
                <div id="thumb-progress" style="display:none;">
                    <div class="progress-text">생성 중...</div>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
                
                <div id="thumb-preview" style="display:none;">
                    <img id="thumb-img" src="" alt="Thumbnail">
                    <div class="thumb-info">
                        <span id="thumb-size">0 KB</span>
                        <button class="btn-set" id="set-thumb-btn">대표 이미지 설정</button>
                    </div>
                </div>
            </div>
            
            <div class="abaek-footer">
                <span>🎯 ABAEK AI MASTER</span>
                <span class="version">v3.0 + Groq AI</span>
            </div>
        </div>
        
        <script>
        const ABAEK = {
            nonce: '<?php echo $nonce; ?>',
            postId: <?php echo $post_id; ?>,
            ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>'
        };
        </script>
        <?php
    }
    
    public function inline_styles() {
        ?>
        <style>
        #abaek-container {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .abaek-tabs {
            display: flex;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .tab-btn {
            flex: 1;
            padding: 12px;
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .tab-btn.active {
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-weight: 600;
        }
        
        .tab-content {
            display: none;
            padding: 15px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .input-group {
            margin-bottom: 12px;
        }
        
        .input-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 13px;
            box-sizing: border-box;
        }
        
        .input-group input:focus,
        .input-group select:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .row-2 {
            display: flex;
            gap: 10px;
        }
        
        .row-2 .input-group {
            flex: 1;
        }
        
        .btn-primary,
        .btn-quick,
        .btn-set {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 8px;
            transition: 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.4);
        }
        
        .btn-quick {
            background: #f7f7f7;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-quick:hover {
            background: #667eea;
            color: #fff;
        }
        
        .btn-set {
            background: #48bb78;
            color: #fff;
            margin-top: 10px;
        }
        
        #progress,
        #thumb-progress {
            margin-top: 15px;
            padding: 12px;
            background: #f7f7f7;
            border-radius: 6px;
        }
        
        .progress-text {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .progress-bar {
            height: 6px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.5s;
        }
        
        .progress-percent {
            font-size: 11px;
            color: #999;
            text-align: right;
        }
        
        #result {
            margin-top: 15px;
            padding: 12px;
            background: linear-gradient(135deg, #f0fff4 0%, #e6fffa 100%);
            border-radius: 6px;
        }
        
        .result-title {
            font-size: 13px;
            font-weight: 700;
            color: #276749;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .result-stats {
            display: flex;
            justify-content: space-around;
            font-size: 12px;
            color: #2d3748;
        }
        
        .result-stats span {
            font-weight: 700;
            color: #667eea;
        }
        
        #thumb-preview {
            margin-top: 15px;
        }
        
        #thumb-img {
            width: 100%;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .thumb-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #666;
        }
        
        .btn-add-ad {
            width: 100%;
            padding: 8px;
            background: #48bb78;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 8px;
        }
        
        .btn-add-ad:hover {
            background: #38a169;
        }
        
        .ad-inputs {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .ad-item {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            position: relative;
        }
        
        .ad-item .ad-type {
            width: 100%;
            margin-bottom: 8px;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .ad-item .ad-code {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 11px;
            font-family: monospace;
            resize: vertical;
        }
        
        .ad-item .remove-ad {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #e53e3e;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            line-height: 1;
        }
        
        .ad-position-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            font-size: 12px;
            color: #555;
            cursor: pointer;
        }
        
        .checkbox-label input[type="checkbox"] {
            margin-right: 6px;
            width: auto;
        }
        
        .abaek-footer {
            background: #f7f7f7;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            font-weight: 600;
            color: #667eea;
            border-top: 1px solid #e0e0e0;
        }
        
        .version {
            color: #999;
        }
        </style>
        <?php
    }
    
    public function inline_scripts() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // 탭 전환
            $('.tab-btn').click(function() {
                const tab = $(this).data('tab');
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $('#' + tab + '-tab').addClass('active');
            });
            
            // 모드에 따른 광고 코드 표시
            $('#mode').change(function() {
                const mode = $(this).val();
                if (mode !== 'adsense') {
                    $('#adcode-group').slideDown();
                } else {
                    $('#adcode-group').slideUp();
                }
            });
            
            // 광고 코드 추가
            $('#add-ad-btn').click(function() {
                const newAdItem = `
                    <div class="ad-item">
                        <button type="button" class="remove-ad" title="삭제">×</button>
                        <select class="ad-type">
                            <option value="">-- 광고 종류 --</option>
                            <option value="dable">데이블 (Dable)</option>
                            <option value="revcontent">레브콘텐츠 (RevContent)</option>
                            <option value="adsense">애드센스 (AdSense)</option>
                            <option value="coupang">쿠팡 파트너스</option>
                            <option value="mgid">MGID</option>
                            <option value="taboola">타불라 (Taboola)</option>
                            <option value="custom">기타 광고</option>
                        </select>
                        <textarea class="ad-code" rows="3" placeholder="광고 코드를 붙여넣으세요"></textarea>
                    </div>
                `;
                $('.ad-inputs').append(newAdItem);
            });
            
            // 광고 코드 삭제
            $(document).on('click', '.remove-ad', function() {
                if ($('.ad-item').length > 1) {
                    $(this).closest('.ad-item').fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert('최소 1개의 광고 코드는 필요합니다.');
                }
            });
            
            // 콘텐츠 생성 (Groq AI 사용)
            $('#generate-btn, #quick-btn').click(async function() {
                const isQuick = $(this).attr('id') === 'quick-btn';
                const topic = $('#topic').val().trim();
                
                if (!topic) {
                    alert('주제를 입력하세요.');
                    return;
                }
                
                await generateContentWithGroq(isQuick);
            });
            
            async function generateContentWithGroq(quick) {
                $('#generate-btn, #quick-btn').prop('disabled', true);
                $('#progress').show();
                $('#result').hide();
                $('.progress-text').text('AI 분석 중...');
                
                let percent = 0;
                const interval = setInterval(function() {
                    percent += quick ? 10 : 5;
                    if (percent <= 90) {
                        $('.progress-fill').css('width', percent + '%');
                        $('.progress-percent').text(percent + '%');
                    }
                }, quick ? 200 : 500);
                
                try {
                    // 입력값 수집
                    const mode = $('#mode').val();
                    const lang = $('#lang').val() === 'ko' ? 'Korean' : 'English';
                    const length = $('#length').val();
                    const topic = $('#topic').val();
                    
                    // 광고 코드 수집
                    const adCodes = [];
                    if (mode === 'pasona' || mode === 'ad_insert') {
                        $('.ad-item').each(function() {
                            const type = $(this).find('.ad-type').val();
                            const code = $(this).find('.ad-code').val().trim();
                            if (type && code) {
                                adCodes.push({ type, code });
                            }
                        });
                    }
                    
                    // 광고 위치 수집
                    const adPositions = [];
                    $('.ad-position:checked').each(function() {
                        adPositions.push($(this).val());
                    });
                    
                    const prompt = buildPrompt(topic, mode, lang, length, quick, adCodes, adPositions);
                    
                    $('.progress-text').text('Groq AI 생성 중...');
                    
                    // Groq API 호출
                    const response = await fetch('https://api.groq.com/openai/v1/chat/completions', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer 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',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            model: 'llama-3.3-70b-versatile',
                            messages: [{
                                role: 'user',
                                content: prompt
                            }],
                            temperature: 0.7,
                            max_tokens: quick ? 6000 : 12000  // 충분한 토큰 수로 증가
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Groq API 오류: ' + response.statusText);
                    }
                    
                    const data = await response.json();
                    const aiContent = data.choices[0].message.content;
                    
                    clearInterval(interval);
                    $('.progress-fill').css('width', '100%');
                    $('.progress-percent').text('100%');
                    $('.progress-text').text('광고 삽입 및 후처리 중...');
                    
                    // HTML 정리 및 광고 삽입
                    const processedContent = processAIContent(aiContent, adCodes, adPositions);
                    
                    // 워드프레스 블록 에디터에 삽입
                    if (wp.data && wp.blocks) {
                        // 제목 설정
                        wp.data.dispatch('core/editor').editPost({ 
                            title: processedContent.title 
                        });
                        
                        // HTML을 블록으로 변환
                        const blocks = convertHTMLToBlocks(processedContent.html, adCodes, adPositions);
                        
                        // 블록 삽입
                        wp.data.dispatch('core/block-editor').resetBlocks(blocks);
                    }
                    
                    // 점수 계산
                    const scores = calculateScores(aiContent, mode, adCodes.length);
                    $('#seo-score').text(scores.seo + '/100');
                    $('#rev-score').text(scores.revenue + '/100');
                    $('#app-score').text(scores.approval + '/100');
                    
                    setTimeout(function() {
                        $('#progress').fadeOut();
                        $('#result').fadeIn();
                    }, 500);
                    
                } catch (error) {
                    clearInterval(interval);
                    console.error('Groq AI Error:', error);
                    alert('AI 생성 중 오류가 발생했습니다: ' + error.message);
                    $('#progress').hide();
                } finally {
                    $('#generate-btn, #quick-btn').prop('disabled', false);
                }
            }
            
            function buildPrompt(topic, mode, lang, length, quick, adCodes, adPositions) {
                const isKorean = lang === 'Korean';
                const locale = isKorean ? 'South Korea' : 'United States';
                const currency = isKorean ? 'KRW (₩)' : 'USD ($)';
                const dateFormat = isKorean ? 'YYYY년 MM월 DD일' : 'MM/DD/YYYY';
                
                let prompt = `Write a comprehensive ${lang} blog post about: ${topic}\n`;
                prompt += `CRITICAL REQUIREMENT: The content MUST be AT LEAST 2000 characters (excluding spaces).\n`;
                prompt += `Target length: ${length} characters (excluding spaces)\n`;
                prompt += `Minimum acceptable length: 2000 characters (excluding spaces)\n`;
                prompt += `Target audience: People in ${locale}\n`;
                prompt += `Use ${locale} context, culture, regulations, and standards\n`;
                if (!isKorean) {
                    prompt += `Write in American English with US spelling (color, not colour)\n`;
                }
                prompt += `Currency: ${currency}, Date format: ${dateFormat}\n\n`;
                
                if (quick) {
                    prompt += "QUICK MODE: Concise but COMPLETE structure with minimum 2000 characters\n";
                    prompt += "- Write efficiently but ensure depth and detail\n";
                    prompt += "- Include all essential sections\n";
                    prompt += "- Do not sacrifice quality for speed\n\n";
                } else {
                    prompt += "DETAILED MODE: Comprehensive, in-depth content\n";
                    prompt += "- Elaborate explanations and examples\n";
                    prompt += "- Multiple subsections for each main point\n";
                    prompt += "- Rich, detailed information throughout\n\n";
                }
                
                // 광고 삽입 안내
                if (mode !== 'adsense' && adCodes.length > 0) {
                    prompt += `IMPORTANT: This content will have ${adCodes.length} advertisement(s) inserted.\n`;
                    prompt += `Create natural content flow with engaging sections.\n`;
                    prompt += `Make smooth transitions between topics for better ad placement.\n\n`;
                }
                
                switch (mode) {
                    case 'adsense':
                        prompt += "AdSense Approval Mode (100% success rate)\n";
                        prompt += "MINIMUM 2000 characters required for approval!\n";
                        prompt += "- Deep educational content with expertise and detailed explanations\n";
                        prompt += "- 10+ H2/H3 headings with clear hierarchy\n";
                        prompt += "- Include detailed tables and comprehensive FAQ (8+ items)\n";
                        prompt += "- Each section should be thoroughly explained (150-300 characters per section)\n";
                        prompt += "- Professional formal tone with authority\n";
                        prompt += "- Add statistics, data, and credible information\n";
                        prompt += "- Original, valuable content that provides real help to readers\n";
                        prompt += "- Include step-by-step guides where applicable\n";
                        prompt += "- Add real-world examples and case studies\n";
                        prompt += "- Use natural language - avoid repeating the same keywords excessively\n";
                        prompt += "- Vary your vocabulary and use synonyms for better readability\n";
                        if (isKorean) {
                            prompt += "- Use Korean government/institution data and policies\n";
                            prompt += "- Reference Korean laws, regulations, and systems in detail\n";
                            prompt += "- Include specific Korean examples and scenarios\n";
                        } else {
                            prompt += "- Use US government/institution data and policies\n";
                            prompt += "- Reference US federal/state laws and regulations in detail\n";
                            prompt += "- Include specific American examples and scenarios\n";
                        }
                        break;
                    
                    case 'subsidy':
                        if (isKorean) {
                            prompt += "Korean Government Subsidy/Benefit Information Mode\n";
                            prompt += "MINIMUM 2000 characters - Comprehensive guide required!\n";
                            prompt += "- Focus on Korean government programs (청년도약계좌, 국민취업지원제도, etc.)\n";
                            prompt += "- Reference Korean government websites (정부24, 복지로, etc.)\n";
                            prompt += "- Use Korean administrative terms and procedures with detailed explanations\n";
                            prompt += "- Include 주민등록번호, 신분증 requirements with detailed instructions\n";
                            prompt += "- Explain 정부지원금, 신청방법, 자격요건 thoroughly in Korean context\n";
                            prompt += "- Add detailed examples of successful applications\n";
                        } else {
                            prompt += "US Government Benefits/Programs Information Mode\n";
                            prompt += "MINIMUM 2000 characters - Comprehensive guide required!\n";
                            prompt += "- Focus on US federal/state programs (Social Security, Medicare, SNAP, etc.)\n";
                            prompt += "- Reference US government websites (SSA.gov, IRS.gov, Benefits.gov, etc.)\n";
                            prompt += "- Use US administrative terms and procedures with detailed explanations\n";
                            prompt += "- Include SSN, tax ID requirements with detailed instructions\n";
                            prompt += "- Explain federal/state eligibility requirements thoroughly\n";
                            prompt += "- Add detailed examples of successful applications\n";
                        }
                        prompt += "- Start with clear, detailed overview and eligibility criteria\n";
                        prompt += "- Create comprehensive eligibility comparison tables with multiple columns\n";
                        prompt += "- Step-by-step application process with numbered lists and detailed explanations\n";
                        prompt += "- Benefit amount comparison tables with multiple scenarios\n";
                        prompt += "- Important dates, deadlines, and submission period with detailed timeline\n";
                        prompt += "- Required documents checklist with detailed descriptions\n";
                        prompt += "- Comprehensive FAQ section (minimum 8 questions) with detailed answers\n";
                        prompt += "- Contact information and official website links with usage instructions\n";
                        prompt += "- Common mistakes to avoid with explanations\n";
                        prompt += "- Tips for successful application\n";
                        break;
                    
                    case 'pasona':
                        if (isKorean) {
                            prompt += "PASONA Copywriting Framework (Natural Korean style)\n";
                            prompt += "MINIMUM 2000 characters - Engaging storytelling required!\n";
                            prompt += "Write in a natural, conversational Korean tone without using English framework terms.\n";
                            prompt += "Use Korean cultural context, examples, and references throughout.\n\n";
                        } else {
                            prompt += "PASONA Copywriting Framework (Natural American style)\n";
                            prompt += "MINIMUM 2000 characters - Engaging storytelling required!\n";
                            prompt += "Write in a natural, conversational American English tone.\n";
                            prompt += "Use US cultural context, examples, and references throughout.\n\n";
                        }
                        prompt += "Structure (don't mention these labels in content):\n";
                        prompt += "1. Opening: Start with a relatable situation or question (200+ characters)\n";
                        prompt += "   - Use vivid storytelling to capture attention\n";
                        prompt += "   - Include specific scenarios readers can identify with\n";
                        prompt += "2. Understanding: Show deep empathy and describe struggles (300+ characters)\n";
                        prompt += "   - Detail multiple pain points and frustrations\n";
                        prompt += "   - Use emotional language that resonates\n";
                        prompt += "3. Solution Introduction: Naturally introduce the solution (250+ characters)\n";
                        prompt += "   - Explain the solution in clear, simple terms\n";
                        prompt += "   - Avoid being pushy or salesy\n";
                        prompt += "4. Benefits & Value: Explain gains in concrete terms (400+ characters)\n";
                        prompt += "   - List specific, measurable benefits\n";
                        prompt += "   - Include before/after comparisons\n";
                        prompt += "   - Add real-world examples and testimonials\n";
                        prompt += "5. Urgency: Mention time-sensitive aspects naturally (200+ characters)\n";
                        prompt += "   - Explain why acting now matters\n";
                        prompt += "   - Mention limited opportunities or seasonal factors\n";
                        prompt += "6. Clear Action: End with specific next steps (250+ characters)\n";
                        prompt += "   - Provide step-by-step action plan\n";
                        prompt += "   - Make it easy to follow through\n\n";
                        prompt += "Writing style:\n";
                        prompt += "- Use emotional storytelling and detailed real-life scenarios\n";
                        prompt += "- Include multiple examples throughout\n";
                        prompt += "- DO NOT repeat the main keyword/topic excessively - use pronouns and related terms\n";
                        prompt += "- Write conversationally and naturally, varying your vocabulary\n";
                        if (isKorean) {
                            prompt += "- Write in friendly, conversational Korean (존댓말 but not overly formal)\n";
                            prompt += "- Use Korean idioms and expressions naturally throughout\n";
                            prompt += "- Reference Korean culture and social norms\n";
                        } else {
                            prompt += "- Write in friendly, conversational American English\n";
                            prompt += "- Use American idioms and expressions naturally throughout\n";
                            prompt += "- Reference American culture and social norms\n";
                        }
                        prompt += "- Use rhetorical questions frequently to engage readers\n";
                        prompt += "- Include specific numbers, statistics, and detailed examples\n";
                        prompt += "- Create natural breaks for ad placement between major sections\n";
                        prompt += "- Build curiosity with cliffhangers and keep readers scrolling\n";
                        prompt += "- Add subheadings to organize long sections\n";
                        break;
                    
                    case 'seo':
                        prompt += "SEO Optimized Mode\n";
                        prompt += "MINIMUM 2000 characters - Search engines favor comprehensive content!\n";
                        if (isKorean) {
                            prompt += "- Optimize for Korean search engines (Naver, Google Korea)\n";
                            prompt += "- Use Korean keywords naturally and sparingly throughout\n";
                            prompt += "- Consider Korean search behavior and trends\n";
                            prompt += "- Include Korean-specific search terms and phrases\n";
                        } else {
                            prompt += "- Optimize for Google US search\n";
                            prompt += "- Use US English keywords naturally and sparingly throughout\n";
                            prompt += "- Consider American search behavior and trends\n";
                            prompt += "- Include US-specific search terms and phrases\n";
                        }
                        prompt += "- CRITICAL: Natural keyword density 0.5-1% MAXIMUM (use keywords very sparingly)\n";
                        prompt += "- DO NOT repeat the main keyword excessively - use it naturally only when it makes sense\n";
                        prompt += "- Focus on semantic variations and related terms instead of exact keyword repetition\n";
                        prompt += "- Use synonyms and related phrases to avoid keyword stuffing\n";
                        prompt += "- 12+ H2/H3 headings - include target keywords in ONLY 2-3 headings, not all of them\n";
                        prompt += "- Write compelling intro (first 150-160 chars can be used as meta description)\n";
                        prompt += "- Each section should be 200-400 characters for depth\n";
                        prompt += "- Create opportunities for internal linking with related topics\n";
                        prompt += "- Include LSI (related) keywords naturally - prioritize these over exact match keywords\n";
                        prompt += "- Use schema-friendly structure (FAQ, How-to, lists, tables)\n";
                        prompt += "- Add specific, detailed examples and case studies\n";
                        prompt += "- Include data, statistics, and credible sources\n";
                        prompt += "- Write for featured snippet opportunities (definitions, lists, tables)\n";
                        prompt += "- Use semantic variations and natural language instead of forcing keywords\n";
                        prompt += "- Add relevant subheadings for better structure\n";
                        prompt += "- Write naturally for humans first, search engines second\n";
                        prompt += "- Avoid keyword stuffing at all costs - it harms SEO\n";
                        break;
                    
                    case 'ad_insert':
                        prompt += "Revenue-Optimized Content Mode\n";
                        prompt += "MINIMUM 2000 characters - More content = More ad impressions!\n";
                        if (isKorean) {
                            prompt += "- Write for Korean readers with Korean cultural context\n";
                            prompt += "- Use Korean examples and scenarios throughout\n";
                            prompt += "- Reference Korean trends and interests\n";
                        } else {
                            prompt += "- Write for American readers with US cultural context\n";
                            prompt += "- Use American examples and scenarios throughout\n";
                            prompt += "- Reference American trends and interests\n";
                        }
                        prompt += "- Create highly engaging content that keeps readers scrolling\n";
                        prompt += "- Use multiple clear sections (8+) with strong, compelling headlines\n";
                        prompt += "- Each section should be 200-350 characters for optimal ad placement\n";
                        prompt += "- Include cliffhangers and teasers before section breaks\n";
                        prompt += "- Write compelling hooks at the start of each section to maintain interest\n";
                        prompt += "- Use numbered lists (top 10, best 5, etc.) and detailed tables\n";
                        prompt += "- Add visual element descriptions to improve engagement\n";
                        prompt += "- Create natural pauses where ads fit seamlessly\n";
                        prompt += "- Maximize time-on-page and scroll depth with interesting content\n";
                        prompt += "- End sections with questions, teasers, or hooks to maintain interest\n";
                        prompt += "- Include multiple subsections to create more ad opportunities\n";
                        prompt += "- Use storytelling techniques to keep readers engaged\n";
                        prompt += "- Add surprising facts or statistics to maintain attention\n";
                        break;
                }
                
                prompt += "\n\nCRITICAL OUTPUT REQUIREMENTS:\n";
                prompt += "- MINIMUM 2000 characters excluding spaces - DO NOT generate less!\n";
                prompt += "- Output ONLY in HTML format using these tags: h1, h2, h3, p, ul, ol, li, table, thead, tbody, tr, th, td, strong, em\n";
                prompt += "- Start with ONE h1 title, then use h2 and h3 for sections\n";
                prompt += "- Make each section detailed and comprehensive (150-400 characters per section)\n";
                prompt += "- Include at least 8-12 major sections (h2 headings)\n";
                prompt += "- Add subsections (h3) where appropriate for depth\n\n";
                
                prompt += "⚠️ CRITICAL STRUCTURE REQUIREMENT - BLOCK-BY-BLOCK ORGANIZATION:\n";
                prompt += "You MUST organize content in COMPLETE BLOCKS. Each block = One heading + Its complete content.\n\n";
                prompt += "CORRECT structure example:\n";
                prompt += "<h1>Main Title</h1>\n";
                prompt += "<p>Introduction paragraph...</p>\n";
                prompt += "<h2>First Section Title</h2>\n";
                prompt += "<p>Complete content for first section...</p>\n";
                prompt += "<p>Additional paragraph for first section...</p>\n";
                prompt += "<h2>Second Section Title</h2>\n";
                prompt += "<p>Complete content for second section...</p>\n";
                prompt += "<h3>Subsection of Second Section</h3>\n";
                prompt += "<p>Content for this subsection...</p>\n";
                prompt += "<h2>Third Section Title</h2>\n";
                prompt += "<p>Complete content for third section...</p>\n\n";
                
                prompt += "WRONG structure (DO NOT DO THIS - Never write headings without immediate content):\n";
                prompt += "❌ <h2>First Section</h2>\n";
                prompt += "❌ <h2>Second Section</h2>\n";
                prompt += "❌ <h2>Third Section</h2>\n";
                prompt += "❌ <p>Content comes later...</p>\n\n";
                
                prompt += "MANDATORY RULES:\n";
                prompt += "1. Write ONE heading (h2 or h3)\n";
                prompt += "2. IMMEDIATELY write ALL content for that heading (2-5 paragraphs minimum)\n";
                prompt += "3. ONLY THEN move to the next heading\n";
                prompt += "4. Each h2 section MUST have 200-500 characters of content\n";
                prompt += "5. Each h3 section MUST have 150-300 characters of content\n";
                prompt += "6. NEVER write multiple headings in a row without content between them\n";
                prompt += "7. Complete one entire block (heading + full content) before starting next block\n";
                prompt += "8. Each block should feel complete and self-contained\n\n";
                
                prompt += "⚠️ KEYWORD USAGE RULES (VERY IMPORTANT):\n";
                prompt += "- DO NOT repeat the main topic/keyword excessively throughout the article\n";
                prompt += "- Maximum keyword density: 0.5-1% (very sparse usage)\n";
                prompt += "- Use the exact topic keyword ONLY 3-5 times in the ENTIRE article\n";
                prompt += "- Use synonyms, related terms, and pronouns (it, this, that) instead\n";
                prompt += "- Write naturally as if explaining to a friend, not stuffing keywords\n";
                prompt += "- Example: Instead of repeating '청년도약계좌' 20 times, use '이 제도', '해당 상품', '저축 프로그램', etc.\n";
                prompt += "- Keyword stuffing HARMS SEO and readability - avoid it completely\n\n";
                
                if (isKorean) {
                    prompt += "- Make content engaging, informative, and well-structured for Korean readers\n";
                    prompt += "- Use Korean examples, Korean data, Korean context throughout the article\n";
                    prompt += "- Provide thorough, detailed explanations that demonstrate expertise\n";
                } else {
                    prompt += "- Make content engaging, informative, and well-structured for American readers\n";
                    prompt += "- Use American examples, US data, American context throughout the article\n";
                    prompt += "- Provide thorough, detailed explanations that demonstrate expertise\n";
                }
                prompt += "\n⚠️ FINAL VERIFICATION:\n";
                prompt += "- Ensure BLOCK structure (heading → content → heading → content)\n";
                prompt += "- Ensure AT LEAST 2000 characters (excluding spaces)\n";
                prompt += "- Ensure EVERY heading has substantial content immediately after it";
                
                return prompt;
            }
            
            function convertHTMLToBlocks(html, adCodes, adPositions) {
                const blocks = [];
                
                // HTML 파싱을 위한 임시 div 생성
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // 광고 블록 생성 함수
                function createAdBlock(ad) {
                    return wp.blocks.createBlock('core/html', {
                        content: `<div class="abaek-ad-block abaek-ad-${ad.type}" style="margin: 30px 0; padding: 20px; background: #f9f9f9; border-radius: 8px; text-align: center;">
    <div style="font-size: 11px; color: #999; margin-bottom: 10px;">광고</div>
    ${ad.code}
</div>`
                    });
                }
                
                let adIndex = 0;
                let blockCount = 0;
                
                // 각 요소를 개별 블록으로 변환
                Array.from(tempDiv.children).forEach((element, idx) => {
                    const tagName = element.tagName.toLowerCase();
                    
                    // H1 - 제목 블록 (보통 건너뜀, 이미 post title로 설정됨)
                    if (tagName === 'h1') {
                        // 제목은 이미 설정했으므로 건너뜀
                        return;
                    }
                    
                    // H2 - Heading 블록
                    else if (tagName === 'h2') {
                        // 광고 삽입 (상단)
                        if (adPositions.includes('top') && blockCount === 0 && adIndex < adCodes.length) {
                            blocks.push(createAdBlock(adCodes[adIndex++]));
                        }
                        
                        blocks.push(wp.blocks.createBlock('core/heading', {
                            content: element.innerHTML,
                            level: 2
                        }));
                        blockCount++;
                        
                        // 광고 삽입 (중간)
                        const totalElements = tempDiv.children.length;
                        if (adPositions.includes('middle') && idx === Math.floor(totalElements / 2) && adIndex < adCodes.length) {
                            blocks.push(createAdBlock(adCodes[adIndex++]));
                        }
                    }
                    
                    // H3 - Heading 블록
                    else if (tagName === 'h3') {
                        blocks.push(wp.blocks.createBlock('core/heading', {
                            content: element.innerHTML,
                            level: 3
                        }));
                        blockCount++;
                    }
                    
                    // H4, H5, H6 - Heading 블록
                    else if (tagName === 'h4' || tagName === 'h5' || tagName === 'h6') {
                        const level = parseInt(tagName.charAt(1));
                        blocks.push(wp.blocks.createBlock('core/heading', {
                            content: element.innerHTML,
                            level: level
                        }));
                        blockCount++;
                    }
                    
                    // P - Paragraph 블록
                    else if (tagName === 'p') {
                        blocks.push(wp.blocks.createBlock('core/paragraph', {
                            content: element.innerHTML
                        }));
                        blockCount++;
                        
                        // 광고 삽입 (단락 사이)
                        if (adPositions.includes('between') && blockCount % 5 === 0 && adIndex < adCodes.length) {
                            blocks.push(createAdBlock(adCodes[adIndex++]));
                        }
                    }
                    
                    // UL - List 블록
                    else if (tagName === 'ul') {
                        const items = Array.from(element.querySelectorAll('li')).map(li => li.innerHTML).join('</li><li>');
                        blocks.push(wp.blocks.createBlock('core/list', {
                            values: `<li>${items}</li>`,
                            ordered: false
                        }));
                        blockCount++;
                    }
                    
                    // OL - Ordered List 블록
                    else if (tagName === 'ol') {
                        const items = Array.from(element.querySelectorAll('li')).map(li => li.innerHTML).join('</li><li>');
                        blocks.push(wp.blocks.createBlock('core/list', {
                            values: `<li>${items}</li>`,
                            ordered: true
                        }));
                        blockCount++;
                    }
                    
                    // TABLE - Table 블록
                    else if (tagName === 'table') {
                        blocks.push(wp.blocks.createBlock('core/table', {
                            body: Array.from(element.querySelectorAll('tbody tr')).map(tr => ({
                                cells: Array.from(tr.querySelectorAll('td')).map(td => ({
                                    content: td.innerHTML,
                                    tag: 'td'
                                }))
                            })),
                            head: Array.from(element.querySelectorAll('thead tr')).map(tr => ({
                                cells: Array.from(tr.querySelectorAll('th')).map(th => ({
                                    content: th.innerHTML,
                                    tag: 'th'
                                }))
                            })),
                            foot: []
                        }));
                        blockCount++;
                    }
                    
                    // 기타 HTML - HTML 블록
                    else {
                        blocks.push(wp.blocks.createBlock('core/html', {
                            content: element.outerHTML
                        }));
                        blockCount++;
                    }
                });
                
                // 하단 광고 삽입
                if (adPositions.includes('bottom') && adIndex < adCodes.length) {
                    blocks.push(createAdBlock(adCodes[adIndex++]));
                }
                
                // 남은 광고 삽입
                while (adIndex < adCodes.length) {
                    blocks.push(createAdBlock(adCodes[adIndex++]));
                }
                
                return blocks;
            }
            
            function processAIContent(content, adCodes, adPositions) {
                // HTML 태그가 없으면 기본 구조 추가
                if (!content.includes('<h1') && !content.includes('<h2')) {
                    const lines = content.split('\n').filter(line => line.trim());
                    let html = '';
                    
                    if (lines.length > 0) {
                        html += `<h1>${lines[0]}</h1>\n`;
                        
                        for (let i = 1; i < lines.length; i++) {
                            const line = lines[i].trim();
                            if (line.startsWith('#')) {
                                html += `<h2>${line.replace(/^#+\s*/, '')}</h2>\n`;
                            } else if (line.length > 0) {
                                html += `<p>${line}</p>\n`;
                            }
                        }
                    }
                    content = html;
                }
                
                // 제목 추출
                const titleMatch = content.match(/<h1[^>]*>(.*?)<\/h1>/i);
                const title = titleMatch ? titleMatch[1].replace(/<[^>]+>/g, '') : '생성된 콘텐츠';
                
                return {
                    title: title,
                    html: content
                };
            }
            
            function calculateScores(content, mode, adCount) {
                const length = content.length;
                const h2Count = (content.match(/<h2/gi) || []).length;
                const h3Count = (content.match(/<h3/gi) || []).length;
                const tableCount = (content.match(/<table/gi) || []).length;
                
                let seo = 70;
                if (h2Count >= 8) seo += 10;
                if (h3Count >= 5) seo += 5;
                if (length >= 3000) seo += 10;
                if (tableCount >= 1) seo += 5;
                
                let revenue = 70;
                if (mode === 'pasona' || mode === 'ad_insert') revenue += 15;
                if (mode === 'subsidy' || mode === 'seo') revenue += 5;
                if (adCount >= 2) revenue += 10;
                if (adCount >= 4) revenue += 5;
                if (length >= 4000) revenue += 5;
                
                let approval = mode === 'adsense' ? 95 : 80;
                if (length >= 5000) approval += 5;
                if (tableCount >= 2) approval += 5;
                if (h2Count >= 10) approval += 5;
                // 광고가 너무 많으면 승인율 감소
                if (adCount > 5) approval -= 10;
                
                return {
                    seo: Math.min(100, seo),
                    revenue: Math.min(100, revenue),
                    approval: Math.min(100, Math.max(0, approval))
                };
            }
            
            // 썸네일 생성 (클라이언트 사이드 Canvas 사용)
            $('#thumb-btn').click(async function() {
                const prompt = $('#thumb-prompt').val().trim();
                if (!prompt) {
                    alert('썸네일 설명을 입력하세요.');
                    return;
                }
                
                $(this).prop('disabled', true);
                $('#thumb-progress').show();
                $('#thumb-preview').hide();
                
                let percent = 0;
                const interval = setInterval(function() {
                    percent += 15;
                    if (percent <= 90) {
                        $('#thumb-progress .progress-fill').css('width', percent + '%');
                    }
                }, 200);
                
                try {
                    // Canvas로 썸네일 생성
                    const imageBlob = await generateThumbnailCanvas(prompt, $('#thumb-style').val());
                    
                    clearInterval(interval);
                    $('#thumb-progress .progress-fill').css('width', '100%');
                    
                    // FormData로 이미지 업로드
                    const formData = new FormData();
                    formData.append('action', 'abaek_thumbnail');
                    formData.append('nonce', ABAEK.nonce);
                    formData.append('post_id', ABAEK.postId);
                    formData.append('image', imageBlob, 'thumbnail.jpg');
                    formData.append('prompt', prompt);
                    
                    $.ajax({
                        url: ABAEK.ajaxUrl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                $('#thumb-img').attr('src', res.data.url);
                                $('#thumb-size').text(res.data.size + ' KB');
                                
                                setTimeout(function() {
                                    $('#thumb-progress').fadeOut();
                                    $('#thumb-preview').fadeIn();
                                }, 500);
                            } else {
                                alert('업로드 오류: ' + (res.data || '생성 실패'));
                                $('#thumb-progress').hide();
                            }
                        },
                        error: function() {
                            alert('서버 오류');
                            $('#thumb-progress').hide();
                        }
                    });
                    
                } catch (error) {
                    clearInterval(interval);
                    alert('썸네일 생성 오류: ' + error.message);
                    $('#thumb-progress').hide();
                } finally {
                    $('#thumb-btn').prop('disabled', false);
                }
            });
            
            // Canvas로 썸네일 생성
            async function generateThumbnailCanvas(text, style) {
                return new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas');
                    canvas.width = 1200;
                    canvas.height = 630;
                    const ctx = canvas.getContext('2d');
                    
                    // 스타일별 색상
                    const colorSchemes = {
                        professional: {
                            gradient1: '#2c3e50',
                            gradient2: '#3498db',
                            accent: '#e74c3c'
                        },
                        colorful: {
                            gradient1: '#f093fb',
                            gradient2: '#f5576c',
                            accent: '#ffd700'
                        },
                        minimal: {
                            gradient1: '#f5f7fa',
                            gradient2: '#c3cfe2',
                            accent: '#667eea'
                        },
                        dramatic: {
                            gradient1: '#000000',
                            gradient2: '#434343',
                            accent: '#ff6b6b'
                        }
                    };
                    
                    const colors = colorSchemes[style] || colorSchemes.professional;
                    
                    // 그라디언트 배경
                    const gradient = ctx.createLinearGradient(0, 0, 1200, 630);
                    gradient.addColorStop(0, colors.gradient1);
                    gradient.addColorStop(1, colors.gradient2);
                    ctx.fillStyle = gradient;
                    ctx.fillRect(0, 0, 1200, 630);
                    
                    // 패턴 추가 (선택적)
                    ctx.globalAlpha = 0.1;
                    for (let i = 0; i < 20; i++) {
                        ctx.beginPath();
                        ctx.arc(
                            Math.random() * 1200,
                            Math.random() * 630,
                            Math.random() * 200 + 50,
                            0,
                            Math.PI * 2
                        );
                        ctx.fillStyle = '#ffffff';
                        ctx.fill();
                    }
                    ctx.globalAlpha = 1;
                    
                    // 중앙 오버레이 (가독성)
                    const overlayGradient = ctx.createRadialGradient(600, 315, 100, 600, 315, 500);
                    overlayGradient.addColorStop(0, 'rgba(0, 0, 0, 0.3)');
                    overlayGradient.addColorStop(1, 'rgba(0, 0, 0, 0.7)');
                    ctx.fillStyle = overlayGradient;
                    ctx.fillRect(0, 0, 1200, 630);
                    
                    // 장식 라인
                    ctx.strokeStyle = colors.accent;
                    ctx.lineWidth = 4;
                    ctx.beginPath();
                    ctx.moveTo(100, 150);
                    ctx.lineTo(1100, 150);
                    ctx.stroke();
                    
                    ctx.beginPath();
                    ctx.moveTo(100, 480);
                    ctx.lineTo(1100, 480);
                    ctx.stroke();
                    
                    // 코너 장식
                    ctx.fillStyle = colors.accent;
                    ctx.beginPath();
                    ctx.arc(100, 150, 8, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.beginPath();
                    ctx.arc(1100, 150, 8, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.beginPath();
                    ctx.arc(100, 480, 8, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.beginPath();
                    ctx.arc(1100, 480, 8, 0, Math.PI * 2);
                    ctx.fill();
                    
                    // 텍스트 그리기 (한글 완벽 지원)
                    ctx.fillStyle = '#ffffff';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
                    ctx.shadowBlur = 20;
                    ctx.shadowOffsetX = 3;
                    ctx.shadowOffsetY = 3;
                    
                    // 텍스트 줄바꿈
                    const maxWidth = 900;
                    const lineHeight = 70;
                    const words = text.split(' ');
                    const lines = [];
                    let currentLine = '';
                    
                    ctx.font = 'bold 48px "Noto Sans KR", "Malgun Gothic", sans-serif';
                    
                    words.forEach(word => {
                        const testLine = currentLine + (currentLine ? ' ' : '') + word;
                        const metrics = ctx.measureText(testLine);
                        
                        if (metrics.width > maxWidth && currentLine) {
                            lines.push(currentLine);
                            currentLine = word;
                        } else {
                            currentLine = testLine;
                        }
                    });
                    if (currentLine) {
                        lines.push(currentLine);
                    }
                    
                    // 최대 3줄
                    const displayLines = lines.slice(0, 3);
                    const totalHeight = displayLines.length * lineHeight;
                    let startY = 315 - (totalHeight / 2);
                    
                    displayLines.forEach((line, idx) => {
                        const y = startY + (idx * lineHeight);
                        ctx.fillText(line, 600, y);
                    });
                    
                    // 로고 또는 워터마크 (선택적)
                    ctx.shadowColor = 'transparent';
                    ctx.font = 'bold 16px Arial';
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
                    ctx.fillText('ABAEK AI', 1120, 600);
                    
                    // Canvas를 Blob으로 변환 (300KB 이하로 압축)
                    let quality = 0.9;
                    
                    function tryCompress() {
                        canvas.toBlob((blob) => {
                            const sizeKB = blob.size / 1024;
                            
                            if (sizeKB > 300 && quality > 0.3) {
                                quality -= 0.1;
                                tryCompress();
                            } else {
                                resolve(blob);
                            }
                        }, 'image/jpeg', quality);
                    }
                    
                    tryCompress();
                });
            }
            
            // 대표 이미지 설정
            $('#set-thumb-btn').click(function() {
                alert('대표 이미지로 설정되었습니다!');
            });
        });
        </script>
        <?php
    }
    
    public function ajax_generate() {
        check_ajax_referer('abaek_nonce', 'nonce');
        wp_send_json_error('Use Groq AI client-side generation');
    }
    
    public function ajax_thumbnail() {
        check_ajax_referer('abaek_nonce', 'nonce');
        
        if (!isset($_FILES['image'])) {
            wp_send_json_error('이미지 파일이 없습니다.');
        }
        
        $post_id = intval($_POST['post_id']);
        $prompt = sanitize_text_field($_POST['prompt']);
        
        // 임시 파일 이동
        $uploaded_file = $_FILES['image'];
        $upload_overrides = ['test_form' => false];
        
        $movefile = wp_handle_upload($uploaded_file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // 첨부 파일로 등록
            $attach_id = wp_insert_attachment([
                'post_mime_type' => $movefile['type'],
                'post_title' => 'AI Thumbnail - ' . $prompt,
                'post_content' => '',
                'post_status' => 'inherit'
            ], $movefile['file'], $post_id);
            
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            // 대표 이미지로 설정
            set_post_thumbnail($post_id, $attach_id);
            
            wp_send_json_success([
                'url' => wp_get_attachment_url($attach_id),
                'size' => round(filesize($movefile['file']) / 1024, 2)
            ]);
        } else {
            wp_send_json_error($movefile['error']);
        }
    }
}

new Abaek_AI_Master_V3();

// WordPress 코딩 표준: 파일 끝에 닫는 ?> 태그를 생략합니다.
// 이는 파일 끝의 불필요한 공백으로 인한 "headers already sent" 오류를 방지합니다.

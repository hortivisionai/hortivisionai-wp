<?php
/**
 * Plugin Name: HortiVision Farmer Dashboard
 * Description: Plant image upload and counting for HortiVision AI
 * Version: 5.0
 */

if (!defined('ABSPATH')) exit;

// ── Config ───────────────────────────────────────────────────
if (!defined('HV_RENDER_BASE')) {
    define('HV_RENDER_BASE', 'https://hortivision-ai-inference.onrender.com');
}

// ── Create the hv_jobs table on activation ───────────────────
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table   = $wpdb->prefix . 'hv_jobs';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        s3_key TEXT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        count INT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// ── AJAX: save an upload result to the database ──────────────
// Called by the browser after Render returns a count.
add_action('wp_ajax_hv_save_result', function () {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in.');
    }
    check_ajax_referer('hv_save_nonce', 'nonce');

    $s3_key   = isset($_POST['s3_key'])   ? sanitize_text_field($_POST['s3_key'])   : '';
    $filename = isset($_POST['filename']) ? sanitize_text_field($_POST['filename']) : '';
    $count    = isset($_POST['count'])    ? intval($_POST['count'])                 : null;

    if ($s3_key === '' || $filename === '') {
        wp_send_json_error('Missing data.');
    }

    global $wpdb;
    $ok = $wpdb->insert(
        $wpdb->prefix . 'hv_jobs',
        array(
            'user_id'    => get_current_user_id(),   // server-side, trusted
            's3_key'     => $s3_key,
            'filename'   => $filename,
            'count'      => $count,
            'created_at' => current_time('mysql'),
        ),
        array('%d', '%s', '%s', '%d', '%s')
    );

    if ($ok === false) {
        wp_send_json_error('Database insert failed.');
    }
    wp_send_json_success(array('id' => $wpdb->insert_id));
});

// ── Shortcode: [hv_upload] ───────────────────────────────────
add_shortcode('hv_upload', function () {

    if (!is_user_logged_in()) {
        return '<p>Please log in to upload images.</p>';
    }

    $user_id    = get_current_user_id();
    $save_nonce = wp_create_nonce('hv_save_nonce');

    ob_start();
    ?>
    <div class="hv-uploader">
        <h2>MagCount</h2>
        <p class="hv-sub">Upload one or more images to count magnolia plants.</p>

        <div class="hv-field">
            <label class="hv-label" for="hv-file">Images</label>
            <input type="file" id="hv-file" accept=".jpg,.jpeg,.png" multiple />
            <p class="hv-hint">Accepted: JPG, JPEG, PNG. You can select multiple files.</p>
        </div>

        <button type="button" id="hv-run" class="hv-btn" disabled>Upload &amp; Count</button>
        <div id="hv-status" class="hv-status" aria-live="polite"></div>

        <div id="hv-result" class="hv-result" hidden>
            <div class="hv-result-head">
                <div class="hv-result-type">Magnolia</div>
                <div class="hv-result-count"><span id="hv-total">0</span></div>
                <div class="hv-result-label">plants counted across <span id="hv-imgcount">0</span> image(s)</div>
            </div>
            <ul id="hv-breakdown" class="hv-breakdown"></ul>
        </div>
    </div>

    <style>
        .hv-uploader { max-width: 560px; }
        .hv-uploader h2 { font-size: 1.4rem; margin: 0 0 .25rem; }
        .hv-sub { color: #566; margin: 0 0 1.5rem; font-size: .95rem; }
        .hv-field { margin-bottom: 1.25rem; }
        .hv-label { display:block; font-weight:600; font-size:.9rem; margin-bottom:.4rem; }
        .hv-uploader input[type=file] {
            width:100%; padding:.6rem .7rem; border:1px solid #cdd6d0;
            border-radius:8px; background:#fff; font-size:.9rem;
        }
        .hv-hint { font-size:.78rem; color:#8a968f; margin:.4rem 0 0; }
        .hv-btn {
            background:#1B4332; color:#fff; border:0; border-radius:8px;
            padding:.75rem 1.4rem; font-size:.95rem; font-weight:600;
            cursor:pointer; width:100%;
        }
        .hv-btn:disabled { background:#c3ccc6; cursor:not-allowed; }
        .hv-btn:not(:disabled):hover { background:#2D6A4F; }
        .hv-status { margin-top:1rem; font-size:.9rem; min-height:1.2em; color:#566; }
        .hv-status.err { color:#a12a2a; }
        .hv-result {
            margin-top:1.5rem; border:1px solid #d8e6dd;
            border-radius:12px; background:#f3f9f5; overflow:hidden;
        }
        .hv-result-head { padding:1.5rem; text-align:center; border-bottom:1px solid #d8e6dd; }
        .hv-result-type {
            font-size:.8rem; letter-spacing:.08em; text-transform:uppercase;
            color:#2D6A4F; font-weight:700;
        }
        .hv-result-count { font-size:3rem; font-weight:800; color:#1B4332; line-height:1.1; }
        .hv-result-label { font-size:.85rem; color:#566; }
        .hv-breakdown { list-style:none; margin:0; padding:.5rem 0; }
        .hv-breakdown li {
            display:flex; justify-content:space-between; align-items:center;
            padding:.6rem 1.5rem; font-size:.9rem; border-top:1px solid #e6f0ea;
        }
        .hv-breakdown li:first-child { border-top:0; }
        .hv-breakdown .hv-fname { color:#334; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:70%; }
        .hv-breakdown .hv-fcount { font-weight:700; color:#1B4332; }
    </style>

    <script>
    (function(){
        var RENDER_BASE = <?php echo json_encode(HV_RENDER_BASE); ?>;
        var USER_ID     = <?php echo json_encode((string)$user_id); ?>;
        var AJAX_URL    = <?php echo json_encode(admin_url('admin-ajax.php')); ?>;
        var SAVE_NONCE  = <?php echo json_encode($save_nonce); ?>;

        var fileInput  = document.getElementById('hv-file');
        var runBtn     = document.getElementById('hv-run');
        var statusEl   = document.getElementById('hv-status');
        var resultEl   = document.getElementById('hv-result');
        var totalEl    = document.getElementById('hv-total');
        var imgCountEl = document.getElementById('hv-imgcount');
        var breakdownEl= document.getElementById('hv-breakdown');

        fileInput.addEventListener('change', function(){
            runBtn.disabled = !fileInput.files.length;
            resultEl.hidden = true;
            statusEl.textContent = '';
            statusEl.className = 'hv-status';
        });

        function setStatus(msg, isErr){
            statusEl.textContent = msg;
            statusEl.className = 'hv-status' + (isErr ? ' err' : '');
        }

        // Upload one file to S3 via presigned URL (per-user key), return {key, name}
        async function uploadOne(file){
            var presignRes = await fetch(RENDER_BASE + '/presign', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: file.name,
                    filetype: file.type || 'image/jpeg',
                    user_id: USER_ID            // <-- NEW: per-user namespacing
                })
            });
            if (!presignRes.ok) throw new Error('Could not prepare upload for ' + file.name);
            var presign = await presignRes.json();

            var putRes = await fetch(presign.upload_url, {
                method: 'PUT',
                headers: { 'Content-Type': file.type || 'image/jpeg' },
                body: file
            });
            if (!putRes.ok) throw new Error('Upload failed for ' + file.name);

            return { key: presign.key, name: file.name };
        }

        // Save one result row to WordPress (which attaches the trusted user id)
        async function saveResult(s3_key, filename, count){
            var body = new URLSearchParams();
            body.append('action', 'hv_save_result');
            body.append('nonce', SAVE_NONCE);
            body.append('s3_key', s3_key);
            body.append('filename', filename);
            body.append('count', count);
            try {
                await fetch(AJAX_URL, { method: 'POST', body: body });
            } catch (e) {
                // non-fatal: the count still shows even if saving fails
                console.error('Save failed for', filename, e);
            }
        }

        runBtn.addEventListener('click', async function(){
            var files = Array.prototype.slice.call(fileInput.files);
            if (!files.length) return;

            runBtn.disabled = true;
            resultEl.hidden = true;
            breakdownEl.innerHTML = '';

            try {
                // 1. Upload each file to S3 sequentially
                var uploaded = [];
                for (var i = 0; i < files.length; i++) {
                    setStatus('Uploading ' + (i+1) + ' of ' + files.length + '…');
                    uploaded.push(await uploadOne(files[i]));
                }

                // 2. Count all via Render
                setStatus('Counting plants across ' + files.length + ' image(s)… this can take a moment.');
                var keys = uploaded.map(function(u){ return u.key; });
                var countRes = await fetch(RENDER_BASE + '/count-s3', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ keys: keys })
                });
                if (!countRes.ok) throw new Error('Counting failed.');
                var data = await countRes.json();

                var countByKey = {};
                (data.per_image || []).forEach(function(row){ countByKey[row.key] = row.count; });

                // 3. Display + save each result
                for (var j = 0; j < uploaded.length; j++) {
                    var u = uploaded[j];
                    var c = (countByKey[u.key] != null) ? countByKey[u.key] : 0;

                    var li = document.createElement('li');
                    var name = document.createElement('span');
                    name.className = 'hv-fname'; name.textContent = u.name;
                    var cnt = document.createElement('span');
                    cnt.className = 'hv-fcount'; cnt.textContent = c;
                    li.appendChild(name); li.appendChild(cnt);
                    breakdownEl.appendChild(li);

                    await saveResult(u.key, u.name, c);   // <-- NEW: persist row
                }

                totalEl.textContent = (data.total_count != null ? data.total_count : 0);
                imgCountEl.textContent = (data.image_count != null ? data.image_count : files.length);
                resultEl.hidden = false;
                setStatus('Done.');
            } catch (err) {
                setStatus(err.message || 'Something went wrong. Please try again.', true);
            } finally {
                runBtn.disabled = false;
            }
        });
    })();
    </script>
    <?php
    return ob_get_clean();
});

// ── Shortcode: [hv_gallery] — user's upload history grid ─────
add_shortcode('hv_gallery', function () {

    if (!is_user_logged_in()) {
        return '<p>Please log in to view your uploads.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table   = $wpdb->prefix . 'hv_jobs';

    // one query: this user's uploads, newest first
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT s3_key, filename, count, created_at
             FROM $table
             WHERE user_id = %d
             ORDER BY created_at DESC",
            $user_id
        )
    );

    if (empty($rows)) {
        return '<div class="hv-gallery-empty">No uploads yet. Your counted images will appear here.</div>';
    }

    // collect keys to fetch view URLs from Render
    $keys = array();
    foreach ($rows as $r) { $keys[] = $r->s3_key; }

    // ask Render for presigned GET urls
    $view_urls = array();
    $resp = wp_remote_post(HV_RENDER_BASE . '/view-urls', array(
        'headers' => array('Content-Type' => 'application/json'),
        'body'    => wp_json_encode(array('keys' => $keys)),
        'timeout' => 20,
    ));
    if (!is_wp_error($resp)) {
        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if (isset($body['urls']) && is_array($body['urls'])) {
            $view_urls = $body['urls'];
        }
    }

    ob_start();
    ?>
    <div class="hv-gallery">
        <h2>My Uploads</h2>
        <div class="hv-grid">
            <?php foreach ($rows as $r):
                $img = isset($view_urls[$r->s3_key]) ? $view_urls[$r->s3_key] : '';
                $date = date('M j, Y', strtotime($r->created_at));
            ?>
                <div class="hv-card">
                    <div class="hv-thumb">
                        <?php if ($img): ?>
                            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($r->filename); ?>" loading="lazy" />
                        <?php else: ?>
                            <div class="hv-thumb-missing">image unavailable</div>
                        <?php endif; ?>
                        <span class="hv-count-badge"><?php echo intval($r->count); ?></span>
                    </div>
                    <div class="hv-card-meta">
                        <span class="hv-card-name" title="<?php echo esc_attr($r->filename); ?>"><?php echo esc_html($r->filename); ?></span>
                        <span class="hv-card-date"><?php echo esc_html($date); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
        .hv-gallery { margin-top: 2.5rem; }
        .hv-gallery h2 { font-size: 1.25rem; margin: 0 0 1rem; }
        .hv-gallery-empty { margin-top:2rem; color:#8a968f; font-size:.9rem; }
        .hv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
        }
        .hv-card {
            border: 1px solid #e2e8f0; border-radius: 10px;
            overflow: hidden; background: #fff;
        }
        .hv-thumb {
            position: relative; aspect-ratio: 4/3; background: #f2f5f3;
            display:flex; align-items:center; justify-content:center;
        }
        .hv-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
        .hv-thumb-missing { font-size:.75rem; color:#aab4ae; }
        .hv-count-badge {
            position:absolute; top:8px; right:8px;
            background:#1B4332; color:#fff; font-weight:700; font-size:.85rem;
            padding:.2rem .55rem; border-radius:999px;
        }
        .hv-card-meta {
            display:flex; justify-content:space-between; align-items:center;
            padding:.55rem .7rem; gap:.5rem;
        }
        .hv-card-name {
            font-size:.8rem; color:#334; overflow:hidden;
            text-overflow:ellipsis; white-space:nowrap;
        }
        .hv-card-date { font-size:.72rem; color:#8a968f; white-space:nowrap; }
    </style>
    <?php
    return ob_get_clean();
});
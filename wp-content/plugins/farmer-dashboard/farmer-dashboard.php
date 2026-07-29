<?php
/**
 * Plugin Name: HortiVision Farmer Dashboard
 * Description: Plant image upload and counting for HortiVision AI
 * Version: 6.0
 */

if (!defined('ABSPATH')) exit;

// ── Config ───────────────────────────────────────────────────
if (!defined('HV_RENDER_BASE')) {
    define('HV_RENDER_BASE', 'https://hortivision-ai-inference.onrender.com');
}

// ── Create / update the hv_jobs table on activation ──────────
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table   = $wpdb->prefix . 'hv_jobs';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        batch_id VARCHAR(64) DEFAULT NULL,
        s3_key TEXT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        count INT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY batch_id (batch_id)
    ) $charset;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// ── Ensure batch_id column exists (for sites that had v5 table) ──
add_action('plugins_loaded', function () {
    global $wpdb;
    $table = $wpdb->prefix . 'hv_jobs';
    $col = $wpdb->get_results($wpdb->prepare(
        "SHOW COLUMNS FROM $table LIKE %s", 'batch_id'
    ));
    if (empty($col)) {
        // add the column if upgrading from an older version
        $wpdb->query("ALTER TABLE $table ADD COLUMN batch_id VARCHAR(64) DEFAULT NULL AFTER user_id");
        $wpdb->query("ALTER TABLE $table ADD KEY batch_id (batch_id)");
    }
});

// ── AJAX: save an upload result to the database ──────────────
add_action('wp_ajax_hv_save_result', function () {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in.');
    }
    check_ajax_referer('hv_save_nonce', 'nonce');

    $s3_key   = isset($_POST['s3_key'])   ? sanitize_text_field($_POST['s3_key'])   : '';
    $filename = isset($_POST['filename']) ? sanitize_text_field($_POST['filename']) : '';
    $count    = isset($_POST['count'])    ? intval($_POST['count'])                 : null;
    $batch_id = isset($_POST['batch_id']) ? sanitize_text_field($_POST['batch_id']) : '';

    if ($s3_key === '' || $filename === '') {
        wp_send_json_error('Missing data.');
    }

    global $wpdb;
    $ok = $wpdb->insert(
        $wpdb->prefix . 'hv_jobs',
        array(
            'user_id'    => get_current_user_id(),
            'batch_id'   => $batch_id,
            's3_key'     => $s3_key,
            'filename'   => $filename,
            'count'      => $count,
            'created_at' => current_time('mysql'),
        ),
        array('%d', '%s', '%s', '%s', '%d', '%s')
    );

    if ($ok === false) {
        wp_send_json_error('Database insert failed.');
    }
    wp_send_json_success(array('id' => $wpdb->insert_id));
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'hv-notification',
        plugin_dir_url(__FILE__) . 'hv-notification.js',
        array(),
        '1.3',
        true
    );
});

add_action('template_redirect', function () {
    if (is_page('get-started')) {   // a page you create with slug "get-started"
        if (is_user_logged_in()) {
            wp_redirect(home_url('/farmer-dashboard/'));
        } else {
            wp_redirect(home_url('/register/'));
        }
        exit;
    }
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

        // Generate a unique batch id for one upload session.
        function makeBatchId(){
            return 'b_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
        }

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

        async function uploadOne(file){
            var presignRes = await fetch(RENDER_BASE + '/presign', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: file.name,
                    filetype: file.type || 'image/jpeg',
                    user_id: USER_ID
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

        async function saveResult(s3_key, filename, count, batchId){
            var body = new URLSearchParams();
            body.append('action', 'hv_save_result');
            body.append('nonce', SAVE_NONCE);
            body.append('s3_key', s3_key);
            body.append('filename', filename);
            body.append('count', count);
            body.append('batch_id', batchId);
            try {
                await fetch(AJAX_URL, { method: 'POST', body: body });
            } catch (e) {
                console.error('Save failed for', filename, e);
            }
        }

        runBtn.addEventListener('click', async function(){
            var files = Array.prototype.slice.call(fileInput.files);
            if (!files.length) return;

            var batchId = makeBatchId();   // one id for this whole upload
            runBtn.disabled = true;
            resultEl.hidden = true;
            breakdownEl.innerHTML = '';

            try {
                var uploaded = [];
                for (var i = 0; i < files.length; i++) {
                    setStatus('Uploading ' + (i+1) + ' of ' + files.length + '…');
                    uploaded.push(await uploadOne(files[i]));
                }

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

                    await saveResult(u.key, u.name, c, batchId);
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

// ── Shortcode: [hv_gallery] — upload history grouped by batch ─
add_shortcode('hv_gallery', function () {

    if (!is_user_logged_in()) {
        return '<p>Please log in to view your uploads.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table   = $wpdb->prefix . 'hv_jobs';

    // Group by batch: one row per upload session.
    // For older rows with no batch_id, fall back to grouping by the minute.
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT
                COALESCE(NULLIF(batch_id,''), DATE_FORMAT(created_at, '%%Y%%m%%d%%H%%i')) AS grp,
                MIN(created_at) AS batch_time,
                COUNT(*)        AS image_count,
                SUM(count)      AS total_count
             FROM $table
             WHERE user_id = %d
             GROUP BY grp
             ORDER BY batch_time DESC",
            $user_id
        )
    );

    if (empty($rows)) {
        return '<div class="hv-gallery-empty">No uploads yet. Your counted images will appear here.</div>';
    }

    ob_start();
    ?>
    <div class="hv-gallery">
        <h2>My Uploads</h2>
        <table class="hv-table">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>Images</th>
                    <th>Total Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r):
                    $when = date('M j, Y g:i A', strtotime($r->batch_time));
                ?>
                    <tr>
                        <td><?php echo esc_html($when); ?></td>
                        <td><?php echo intval($r->image_count); ?></td>
                        <td class="hv-total-cell"><?php echo intval($r->total_count); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <style>
        .hv-gallery { margin-top: 2.5rem; }
        .hv-gallery h2 { font-size: 1.25rem; margin: 0 0 1rem; }
        .hv-gallery-empty { margin-top:2rem; color:#8a968f; font-size:.9rem; }
        .hv-table {
            width:100%; border-collapse:collapse; background:#fff;
            border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;
        }
        .hv-table thead th {
            text-align:left; font-size:.8rem; letter-spacing:.03em;
            text-transform:uppercase; color:#2D6A4F; background:#f3f9f5;
            padding:.8rem 1rem; border-bottom:1px solid #d8e6dd;
        }
        .hv-table tbody td {
            padding:.85rem 1rem; font-size:.92rem; color:#334;
            border-top:1px solid #eef3f0;
        }
        .hv-table tbody tr:first-child td { border-top:0; }
        .hv-table .hv-total-cell { font-weight:700; color:#1B4332; }
        .hv-table tbody tr:hover { background:#f8fbf9; }
    </style>
    <?php
    return ob_get_clean();
});
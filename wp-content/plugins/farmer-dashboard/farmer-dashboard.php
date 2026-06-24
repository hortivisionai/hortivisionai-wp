<?php
/**
 * Plugin Name: HortiVision Farmer Dashboard
 * Description: Custom user dashboard tabs for HortiVision AI
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

// ── Create database table on activation ─────────────────────
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $table   = $wpdb->prefix . 'hv_jobs';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        category VARCHAR(100) NOT NULL,
        file_url TEXT NOT NULL,
        file_path TEXT NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        result TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// ── Register 3 custom tabs ───────────────────────────────────
add_filter('um_account_page_default_tabs_hook', function($tabs) {

    $tabs[100]['new-upload'] = array(
        'icon'        => 'um-faicon-upload',
        'title'       => 'New Upload',
        'custom'      => true,
        'show_button' => false,
    );

    $tabs[200]['upload-history'] = array(
        'icon'        => 'um-faicon-history',
        'title'       => 'Upload History',
        'custom'      => true,
        'show_button' => false,
    );

    $tabs[300]['personal-details'] = array(
        'icon'        => 'um-faicon-user',
        'title'       => 'Personal Details',
        'custom'      => true,
        'show_button' => false,
    );

    return $tabs;
});

// ── Make tabs accessible ─────────────────────────────────────
add_filter('um_account_tab__new-upload', function($info) {
    $info[0] = 'New Upload';
    return $info;
});
add_filter('um_account_tab__upload-history', function($info) {
    $info[0] = 'Upload History';
    return $info;
});
add_filter('um_account_tab__personal-details', function($info) {
    $info[0] = 'Personal Details';
    return $info;
});

// ── Tab 1: New Upload ────────────────────────────────────────
add_action('um_account_tab_content__new-upload', function() {
    ?>
    <div class="hv-tab-content">
        <h3>New Upload</h3>
        <p>Select a category to begin your upload.</p>

        <div class="hv-upload-form">

            <div class="hv-field">
                <label class="hv-field-label">Category</label>
                <div class="hv-radio-group">

                    <label class="hv-radio-option">
                        <input type="radio" name="hv_category" value="fruits" />
                        <span>Fruits</span>
                    </label>

                    <label class="hv-radio-option">
                        <input type="radio" name="hv_category" value="flowers" />
                        <span>Flowers</span>
                    </label>

                    <label class="hv-radio-option">
                        <input type="radio" name="hv_category" value="ornamentals" />
                        <span>Ornamentals</span>
                    </label>

                </div>
            </div>

            <div class="hv-field" id="hv-file-field" style="display:none;">
                <label class="hv-field-label">Upload File</label>
                <input
                    type="file"
                    id="hv-file"
                    accept=".bil,.hdr,.raw,.jpeg,.jpg,.png"
                />
                <p class="hv-hint">Accepted: .bil .hdr .raw .jpeg .jpg .png</p>
            </div>

            <button
                type="button"
                id="hv-submit"
                class="hv-btn"
                disabled
            >
                Upload File
            </button>

            <div id="hv-status" class="hv-status"></div>

        </div>
    </div>

    <style>
        .hv-tab-content {
            padding: 10px 0;
            max-width: 560px;
        }
        .hv-tab-content h3 {
            font-size: 20px;
            margin-bottom: 6px;
        }
        .hv-tab-content > p {
            color: #666;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .hv-field {
            margin-bottom: 20px;
        }
        .hv-field-label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .hv-radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .hv-radio-option {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 14px;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            transition: border-color 0.2s;
        }
        .hv-radio-option:hover {
            border-color: #1B4332;
        }
        .hv-radio-option input[type="radio"] {
            accent-color: #1B4332;
            width: 16px;
            height: 16px;
        }
        .hv-radio-option input[type="radio"]:checked + span {
            font-weight: 600;
            color: #1B4332;
        }
        .hv-field input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        .hv-hint {
            font-size: 12px;
            color: #888;
            margin-top: 6px;
        }
        .hv-btn {
            background: #1B4332;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 8px;
        }
        .hv-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .hv-btn:not(:disabled):hover {
            background: #2D6A4F;
        }
        .hv-status {
            margin-top: 16px;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            display: none;
        }
        .hv-status.success {
            background: #D8F3DC;
            color: #1B4332;
            display: block;
        }
        .hv-status.error {
            background: #fee2e2;
            color: #991b1b;
            display: block;
        }
        .hv-status.loading {
            background: #f4f4f4;
            color: #333;
            display: block;
        }
        .hv-detail-row {
            display: flex;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .hv-detail-label {
            font-weight: 600;
            width: 120px;
            color: #444;
        }
        .hv-detail-value {
            color: #222;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var radios    = document.querySelectorAll('input[name="hv_category"]');
        var fileField = document.getElementById('hv-file-field');
        var submitBtn = document.getElementById('hv-submit');
        var status    = document.getElementById('hv-status');

        radios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    fileField.style.display = 'block';
                    submitBtn.disabled = false;
                }
            });
        });

        submitBtn.addEventListener('click', function() {
            var file     = document.getElementById('hv-file').files[0];
            var category = document.querySelector('input[name="hv_category"]:checked');

            if (!file) {
                status.className = 'hv-status error';
                status.textContent = 'Please select a file to upload.';
                return;
            }

            status.className = 'hv-status loading';
            status.textContent = 'Uploading... please wait.';
            submitBtn.disabled = true;

            var formData = new FormData();
            formData.append('action', 'hv_upload_file');
            formData.append('hv_category', category.value);
            formData.append('hv_file', file);
            formData.append('nonce', '<?php echo wp_create_nonce("hv_upload_nonce"); ?>');

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    status.className = 'hv-status success';
                    status.textContent = 'File uploaded successfully!';
                    document.getElementById('hv-file').value = '';
                    radios.forEach(function(r) { r.checked = false; });
                    fileField.style.display = 'none';
                    submitBtn.disabled = true;
                } else {
                    status.className = 'hv-status error';
                    status.textContent = 'Upload failed: ' + data.data;
                    submitBtn.disabled = false;
                }
            })
            .catch(function() {
                status.className = 'hv-status error';
                status.textContent = 'Something went wrong. Please try again.';
                submitBtn.disabled = false;
            });
        });
    });
    </script>
    <?php
});

// ── Tab 2: Upload History (blank for now) ────────────────────
add_action('um_account_tab_content__upload-history', function() {
    ?>
    <div class="hv-tab-content">
        <h3>Upload History</h3>
        <p style="color:#666; font-size:14px;">Your past uploads and results will appear here.</p>
    </div>
    <?php
});

// ── Tab 3: Personal Details ──────────────────────────────────
add_action('um_account_tab_content__personal-details', function() {
    $user = wp_get_current_user();
    $name = trim($user->first_name . ' ' . $user->last_name);
    if (empty($name)) $name = $user->display_name;
    ?>
    <div class="hv-tab-content">
        <h3>Personal Details</h3>

        <div class="hv-detail-row">
            <span class="hv-detail-label">Name</span>
            <span class="hv-detail-value"><?php echo esc_html($name); ?></span>
        </div>

        <div class="hv-detail-row">
            <span class="hv-detail-label">Email</span>
            <span class="hv-detail-value"><?php echo esc_html($user->user_email); ?></span>
        </div>

    </div>
    <?php
});

// ── AJAX: Handle file upload ─────────────────────────────────
add_action('wp_ajax_hv_upload_file', function() {

    if (!wp_verify_nonce($_POST['nonce'], 'hv_upload_nonce')) {
        wp_send_json_error('Security check failed.');
    }

    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in.');
    }

    if (empty($_FILES['hv_file'])) {
        wp_send_json_error('No file received.');
    }

    $allowed_categories = ['fruits', 'flowers', 'ornamentals'];
    $category = sanitize_text_field($_POST['hv_category']);
    if (!in_array($category, $allowed_categories)) {
        wp_send_json_error('Invalid category.');
    }

    $file        = $_FILES['hv_file'];
    $allowed_ext = ['bil', 'hdr', 'raw', 'jpeg', 'jpg', 'png'];
    $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        wp_send_json_error('File type not allowed. Accepted: .bil .hdr .raw .jpeg .jpg .png');
    }

    $max_size = 300 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        wp_send_json_error('File too large. Maximum size is 300MB.');
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (isset($upload['error'])) {
        wp_send_json_error($upload['error']);
    }

    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'hv_jobs', [
        'user_id'    => get_current_user_id(),
        'category'   => $category,
        'file_url'   => $upload['url'],
        'file_path'  => $upload['file'],
        'status'     => 'pending',
        'created_at' => current_time('mysql')
    ]);

    wp_send_json_success('File uploaded successfully.');
});
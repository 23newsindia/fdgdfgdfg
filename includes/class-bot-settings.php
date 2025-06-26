<?php
// includes/class-bot-settings.php

if (!defined('ABSPATH')) {
    exit;
}

class BotSettings {
    public function add_bot_settings_section($settings) {
        // Add bot blocking settings to the main settings class
        $settings->add_settings_section('bot-blocking', 'Bot Blocking', array($this, 'render_bot_settings'));
        return $settings;
    }
    
    public function render_bot_settings() {
        $options = array(
            'enable_bot_blocking' => get_option('security_enable_bot_blocking', true),
            'bot_skip_logged_users' => get_option('security_bot_skip_logged_users', true),
            'bot_max_requests_per_minute' => get_option('security_bot_max_requests_per_minute', 30),
            'bot_block_threshold' => get_option('security_bot_block_threshold', 5),
            'bot_block_message' => get_option('security_bot_block_message', 'Access Denied: Automated requests not allowed.'),
            'bot_log_retention_days' => get_option('security_bot_log_retention_days', 30)
        );
        ?>
        <div id="bot-blocking-tab" class="tab-content" style="display:none;">
            <table class="form-table">
                <tr>
                    <th>Enable Bot Blocking</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_bot_blocking" value="1" <?php checked($options['enable_bot_blocking']); ?>>
                            Enable automatic bot detection and blocking
                        </label>
                        <p class="description">Automatically detects and blocks malicious bots and scrapers</p>
                    </td>
                </tr>
                
                <tr>
                    <th>Skip Logged-in Users</th>
                    <td>
                        <label>
                            <input type="checkbox" name="bot_skip_logged_users" value="1" <?php checked($options['bot_skip_logged_users']); ?>>
                            Skip bot detection for logged-in users
                        </label>
                        <p class="description">Recommended to avoid blocking legitimate users</p>
                    </td>
                </tr>
                
                <tr>
                    <th>Rate Limiting</th>
                    <td>
                        <label>
                            Max requests per minute:
                            <input type="number" name="bot_max_requests_per_minute" value="<?php echo esc_attr($options['bot_max_requests_per_minute']); ?>" min="5" max="200">
                        </label>
                        <p class="description">Maximum requests allowed per IP per minute before flagging as bot</p>
                    </td>
                </tr>
                
                <tr>
                    <th>Block Threshold</th>
                    <td>
                        <label>
                            Block after:
                            <input type="number" name="bot_block_threshold" value="<?php echo esc_attr($options['bot_block_threshold']); ?>" min="1" max="50">
                            suspicious activities
                        </label>
                        <p class="description">Number of suspicious activities before permanently blocking an IP</p>
                    </td>
                </tr>
                
                <tr>
                    <th>Block Message</th>
                    <td>
                        <textarea name="bot_block_message" rows="3" cols="50" class="large-text"><?php echo esc_textarea($options['bot_block_message']); ?></textarea>
                        <p class="description">Message shown to blocked bots</p>
                    </td>
                </tr>
                
                <tr>
                    <th>Log Retention</th>
                    <td>
                        <label>
                            Keep logs for:
                            <input type="number" name="bot_log_retention_days" value="<?php echo esc_attr($options['bot_log_retention_days']); ?>" min="1" max="365">
                            days
                        </label>
                        <p class="description">How long to keep bot activity logs (blocked IPs are kept indefinitely)</p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    public function save_bot_settings() {
        // Save bot blocking settings
        update_option('security_enable_bot_blocking', isset($_POST['enable_bot_blocking']));
        update_option('security_bot_skip_logged_users', isset($_POST['bot_skip_logged_users']));
        update_option('security_bot_max_requests_per_minute', intval($_POST['bot_max_requests_per_minute']));
        update_option('security_bot_block_threshold', intval($_POST['bot_block_threshold']));
        update_option('security_bot_block_message', sanitize_textarea_field($_POST['bot_block_message']));
        update_option('security_bot_log_retention_days', intval($_POST['bot_log_retention_days']));
    }
    
    public function register_bot_settings() {
        $settings = array(
            'security_enable_bot_blocking',
            'security_bot_skip_logged_users',
            'security_bot_max_requests_per_minute',
            'security_bot_block_threshold',
            'security_bot_block_message',
            'security_bot_log_retention_days'
        );
        
        foreach ($settings as $setting) {
            register_setting('security_settings', $setting);
        }
    }
}
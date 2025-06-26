<?php
// includes/class-bot-dashboard.php

if (!defined('ABSPATH')) {
    exit;
}

class BotDashboard {
    private $bot_blocker;
    
    public function __construct($bot_blocker) {
        $this->bot_blocker = $bot_blocker;
    }
    
    public function init() {
        add_action('admin_menu', array($this, 'add_dashboard_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_dashboard_scripts'));
    }
    
    public function add_dashboard_page() {
        add_submenu_page(
            'security-settings',
            'Bot Blocker Dashboard',
            'Bot Dashboard',
            'manage_options',
            'security-bot-dashboard',
            array($this, 'render_dashboard_page')
        );
    }
    
    public function enqueue_dashboard_scripts($hook) {
        if ($hook !== 'security-settings_page_security-bot-dashboard') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'bot-dashboard',
            plugin_dir_url(dirname(__FILE__)) . 'assets/bot-dashboard.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('bot-dashboard', 'botDashboard', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('security_bot_stats'),
            'unblock_nonce' => wp_create_nonce('security_bot_unblock')
        ));
        
        wp_enqueue_style(
            'bot-dashboard',
            plugin_dir_url(dirname(__FILE__)) . 'assets/bot-dashboard.css',
            array(),
            '1.0.0'
        );
    }
    
    public function render_dashboard_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $blocked_bots = $this->bot_blocker->get_blocked_bots(20);
        $recent_activity = $this->bot_blocker->get_bot_activity(30);
        ?>
        <div class="wrap">
            <h1><span class="dashicons dashicons-shield-alt"></span> Bot Blocker Dashboard</h1>
            
            <div class="bot-dashboard-stats">
                <div class="bot-stat-card">
                    <h3>Total Blocked</h3>
                    <div class="stat-number" id="total-blocked">Loading...</div>
                </div>
                <div class="bot-stat-card">
                    <h3>Blocked Today</h3>
                    <div class="stat-number" id="today-blocked">Loading...</div>
                </div>
                <div class="bot-stat-card">
                    <h3>Blocked This Week</h3>
                    <div class="stat-number" id="week-blocked">Loading...</div>
                </div>
            </div>
            
            <div class="bot-dashboard-content">
                <div class="bot-dashboard-section">
                    <h2>Currently Blocked IPs</h2>
                    <div class="bot-table-container">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Hits</th>
                                    <th>Reason</th>
                                    <th>Last Seen</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($blocked_bots)): ?>
                                    <tr>
                                        <td colspan="5">No blocked bots found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($blocked_bots as $bot): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html($bot->ip_address); ?></strong>
                                                <div class="bot-user-agent"><?php echo esc_html(substr($bot->user_agent, 0, 100)); ?>...</div>
                                            </td>
                                            <td><?php echo esc_html($bot->hits); ?></td>
                                            <td><?php echo esc_html($bot->blocked_reason); ?></td>
                                            <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($bot->last_seen))); ?></td>
                                            <td>
                                                <button class="button unblock-bot" data-ip="<?php echo esc_attr($bot->ip_address); ?>">
                                                    Unblock
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bot-dashboard-section">
                    <h2>Recent Bot Activity</h2>
                    <div class="bot-table-container">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                    <th>Hits</th>
                                    <th>Reason</th>
                                    <th>Last Request</th>
                                    <th>Last Seen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_activity)): ?>
                                    <tr>
                                        <td colspan="6">No recent activity found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_activity as $activity): ?>
                                        <tr class="<?php echo $activity->is_blocked ? 'blocked-row' : 'warning-row'; ?>">
                                            <td>
                                                <strong><?php echo esc_html($activity->ip_address); ?></strong>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $activity->is_blocked ? 'blocked' : 'monitoring'; ?>">
                                                    <?php echo $activity->is_blocked ? 'Blocked' : 'Monitoring'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo esc_html($activity->hits); ?></td>
                                            <td><?php echo esc_html($activity->blocked_reason); ?></td>
                                            <td>
                                                <code><?php echo esc_html(substr($activity->request_uri, 0, 50)); ?>...</code>
                                            </td>
                                            <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($activity->last_seen))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bot-dashboard-section">
                    <h2>Top Blocked IPs</h2>
                    <div id="top-blocked-ips">Loading...</div>
                </div>
            </div>
        </div>
        <?php
    }
}
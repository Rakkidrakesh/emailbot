<?php
// BOT_NAME should be defined in config.php (which is included by index.php before this footer)
?>
            </div> <!-- /.content_wrap -->
        </div> <!-- /.middle_inner -->
    </main> <!-- /#middle -->

    <!-- Chatbot Integration -->
    <div class="chatbot-wrapper" id="gfs-chatbot-wrapper">
      <button class="chat-toggle-button" id="chat-toggle" title="Chat with <?php echo htmlspecialchars(BOT_NAME, ENT_QUOTES, 'UTF-8'); ?>">
        <i class="fas fa-comment-dots"></i>
      </button>
      <div class="chat-container" id="chat-container">
        <div class="chat-header">
          <div class="logo">
            <img src="assets/images/bot-avatar.jpg" alt="Bot Avatar"> <!-- Assuming .jpg from previous logs -->
            <h1><?php echo htmlspecialchars(BOT_NAME, ENT_QUOTES, 'UTF-8'); ?></h1>
          </div>
          <div class="actions">
            <!-- MODIFIED ICON AND TITLE FOR REFRESH BUTTON -->
            <button id="clear-chat-btn" class="clear-btn" title="Refresh Chat"><i class="fas fa-sync-alt"></i></button>
            <button id="close-chat-btn" class="close-btn" title="Close Chat"><i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="chat-messages" id="chat-messages">
           <div class="message bot-message">
                <div class="message-avatar"><img src="assets/images/bot-avatar.jpg" alt="Bot"></div> <!-- Assuming .jpg -->
                <div class="message-content">
                    <div class="message-text"><p>Hi there! I'm the <?php echo htmlspecialchars(BOT_NAME, ENT_QUOTES, 'UTF-8'); ?>. How can I assist you today?</p></div>
                    <div class="message-time" id="initial-bot-time"><?php echo date('h:i A'); ?></div>
                </div>
            </div>
        </div>
        <div class="chat-input">
          <form id="chat-form">
            <textarea id="user-input" placeholder="Type your message..." rows="1" required aria-label="Chat message input"></textarea>
            <button type="submit" id="send-btn" disabled title="Send Message"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      </div>
    </div>
    <!-- End Chatbot Integration -->

    <footer class="site-footer">
        <p>© <?php echo date("Y"); ?> Global Feeder Shipping. All Rights Reserved.</p>
    </footer>
</div> <!-- /.site-container -->
<script src="assets/js/script.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/script.js'); ?>"></script>
<script src="assets/js/chatbot_script.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/chatbot_script.js'); ?>"></script>
<?php
// Inject service routes data for JavaScript if on the service-routes page
if (isset($current_page) && $current_page === 'service-routes' && isset($service_routes_data)) {
    echo '<script type="text/javascript">';
    echo 'const routesDataFromPHP = ' . json_encode($service_routes_data) . ';';
    echo '</script>';
}
?>
</body>
</html>
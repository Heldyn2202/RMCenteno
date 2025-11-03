<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .navbar {
            background-color: #0084ff;
            color: white;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .navbar-logo {
            font-weight: bold;
            font-size: 22px;
            margin-right: 20px;
        }

        .navbar-search {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            width: 240px;
        }

        .navbar-search i {
            margin-right: 8px;
            font-size: 14px;
        }

        .navbar-search input {
            background: transparent;
            border: none;
            color: white;
            width: 100%;
            outline: none;
        }

        .navbar-search input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .navbar-right {
            display: flex;
            align-items: center;
        }

        .navbar-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            cursor: pointer;
        }

        .navbar-icon:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        .sidebar {
            width: 360px;
            background-color: white;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .sidebar-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
        }

        .sidebar-tabs {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
        }

        .sidebar-tab {
            padding: 12px 0;
            flex: 1;
            text-align: center;
            font-weight: 600;
            color: #65676b;
            cursor: pointer;
            position: relative;
        }

        .sidebar-tab.active {
            color: #0084ff;
        }

        .sidebar-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #0084ff;
        }

        .search-container {
            padding: 10px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .search-box {
            background-color: #f0f2f5;
            border-radius: 20px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
        }

        .search-box i {
            color: #65676b;
            margin-right: 8px;
        }

        .search-box input {
            background: transparent;
            border: none;
            width: 100%;
            outline: none;
            font-size: 15px;
        }

        .contacts-list {
            flex: 1;
            overflow-y: auto;
        }

        .contact-item {
            display: flex;
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
        }

        .contact-item:hover {
            background-color: #f8f9fa;
            transform: translateX(4px);
        }

        .contact-item.active {
            background-color: #e6f2ff;
        }

        .contact-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00aaff, #007bff);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            margin-right: 12px;
        }

        .contact-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .contact-name {
            font-weight: 600;
            margin-bottom: 4px;
            color: #050505;
        }

        .contact-preview {
            font-size: 13px;
            color: #65676b;
            display: flex;
            align-items: center;
        }

        .contact-preview.you {
            color: #050505;
        }

        .contact-time {
            font-size: 12px;
            color: #65676b;
            margin-top: 4px;
        }

        .unread-badge {
            background-color: #0084ff;
            color: white;
            font-size: 12px;
            font-weight: bold;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
        }

        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: #f0f2f5;
            position: relative;
        }

        .chat-header {
            background-color: white;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            z-index: 10;
            position: relative;
        }

        .chat-header-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00aaff, #007bff);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            margin-right: 12px;
        }

        .chat-header-info {
            flex: 1;
        }

        .chat-header-name {
            font-weight: 600;
            color: #050505;
        }

        .chat-header-status {
            font-size: 13px;
            color: #0084ff;
        }

        .contact-status {
            display: flex;
            align-items: center;
            font-size: 13px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #42b883;
            margin-right: 6px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .loading-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 12px;
            color: #0084ff;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .loading-indicator.active {
            opacity: 1;
        }

        .chat-header-actions {
            display: flex;
        }

        .chat-header-action {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
            cursor: pointer;
            color: #65676b;
        }

        .chat-header-action:hover {
            background-color: #f0f2f5;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            padding-bottom: 80px;
            scroll-behavior: smooth;
        }

        .message {
            max-width: 65%;
            margin-bottom: 16px;
            display: flex;
        }

        .message.received {
            align-self: flex-start;
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00aaff, #007bff);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin: 0 8px;
        }

        .message.sent .message-avatar {
            display: none;
        }

        .message-content {
            padding: 8px 12px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        }

        .message.received .message-content {
            background-color: white;
            border-top-left-radius: 4px;
        }

        .message.sent .message-content {
            background-color: #0084ff;
            color: white;
            border-top-right-radius: 4px;
        }

        .message-text {
            font-size: 15px;
            line-height: 1.4;
        }

        .message-time {
            font-size: 11px;
            text-align: right;
            margin-top: 4px;
            opacity: 0.7;
        }

        .message-status {
            font-size: 11px;
            margin-left: 5px;
        }

        .message-status.sent-single {
            opacity: 0.7;
        }

        .message-status.read-double {
            color: #4CAF50;
        }

        .message-fade-in {
            animation: messageFadeIn 0.5s ease-out;
        }

        @keyframes messageFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Barra de mensajes fija */
        .message-input-container {
            position: fixed;
            bottom: 0;
            left: 360px;
            right: 0;
            padding: 16px 20px;
            background-color: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            z-index: 100;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .message-input-action {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
            cursor: pointer;
            color: #65676b;
            transition: all 0.2s ease;
        }

        .message-input-action:hover {
            background-color: #f0f2f5;
            transform: scale(1.05);
        }

        .message-input {
            flex: 1;
            padding: 10px 15px;
            border-radius: 20px;
            border: none;
            background-color: #f0f2f5;
            font-size: 15px;
            margin: 0 5px;
            outline: none;
            resize: none;
            max-height: 120px;
        }

        .send-button {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #0084ff;
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
            transition: all 0.2s ease;
            transform: scale(1);
        }

        .send-button:hover {
            background-color: #0073e6;
            transform: scale(1.05);
        }

        .send-button:active {
            transform: scale(0.95);
        }

        .send-button:disabled {
            background-color: #b3d7ff;
            cursor: not-allowed;
            transform: scale(1);
        }

        .upload-progress {
            width: 100%;
            height: 3px;
            background-color: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
            display: none;
        }

        .upload-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #0084ff, #00c6ff);
            width: 0%;
            transition: width 0.3s ease;
        }

        .sticker-message {
            font-size: 40px;
        }

        .missed-call {
            display: flex;
            align-items: center;
            color: #f5535e;
            font-size: 14px;
        }

        .missed-call i {
            margin-right: 5px;
        }

        .chat-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #65676b;
            text-align: center;
        }

        .chat-empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .file-link {
            display: block;
            margin-top: 8px;
            padding: 6px 10px;
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }

        .file-link i {
            margin-right: 5px;
        }

        .message-image, .message-video {
            margin-bottom: 10px;
            cursor: pointer;
        }

        .message-image img, .message-video video {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .typing-indicator {
            padding: 8px 12px;
            background-color: white;
            border-radius: 18px;
            align-self: flex-start;
            margin-bottom: 10px;
            font-style: italic;
            color: #65676b;
            font-size: 14px;
            display: none;
            animation: typingPulse 1.5s infinite;
        }

        @keyframes typingPulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .new-messages-indicator {
            position: absolute;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: #0084ff;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 90;
            display: none;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0) translateX(-50%);}
            40% {transform: translateY(-5px) translateX(-50%);}
            60% {transform: translateY(-3px) translateX(-50%);}
        }

        .lightbox-modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
            align-items: center;
            justify-content: center;
        }

        .lightbox-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
        }

        .lightbox-content.video {
            width: auto;
            height: auto;
        }

        .lightbox-caption {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            text-align: center;
            color: #ccc;
            padding: 10px 0;
            height: 150px;
        }

        .lightbox-close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
        }

        .lightbox-close:hover,
        .lightbox-close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        .context-menu {
            position: absolute;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            list-style: none;
            padding: 8px 0;
            z-index: 1000;
        }

        .context-menu .list-group-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 14px;
        }

        .context-menu .list-group-item:hover {
            background-color: #f0f0f0;
        }

        .edited-tag {
            font-size: 0.6rem;
            margin-left: 5px;
            opacity: 0.6;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0084ff;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .notification.toast-success {
            background: #42b883;
        }

        .notification-content {
            display: flex;
            align-items: center;
        }

        .notification-content i {
            margin-right: 8px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Scrollbar styling */
        .contacts-list::-webkit-scrollbar,
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .contacts-list::-webkit-scrollbar-track,
        .chat-messages::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        .contacts-list::-webkit-scrollbar-thumb,
        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .contacts-list::-webkit-scrollbar-thumb:hover,
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }
        
        /* Estilos para el botón de me gusta activo */
        .like-button.active {
            color: #ff4081;
        }
        
        .like-count {
            font-size: 12px;
            margin-left: 4px;
            color: #65676b;
        }
        
        .liked-message {
            position: relative;
        }
        
        .liked-message::after {
            content: "❤️";
            position: absolute;
            bottom: -5px;
            right: -5px;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <div class="main-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Chat SIGI</div>
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchContact" placeholder="Buscar contacto...">
                    </div>
                </div>
            </div>
            <div class="sidebar-tabs">
                <div class="sidebar-tab active">Activos</div>
                <div class="sidebar-tab">Comunidad</div>
                <div class="sidebar-tab">Todos</div>
            </div>
            <div class="contacts-list">
                <div id="contactsContainer">
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Cargando contactos...
                    </div>
                </div>
            </div>
        </div>

        <div class="chat-area">
            <div id="chatHeader" class="chat-header">
                <div class="d-flex align-items-center">
                    <div class="chat-header-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="chat-header-info">
                        <div class="chat-header-name">Selecciona un contacto</div>
                        <div class="chat-header-status">Para comenzar a chatear</div>
                    </div>
                </div>
            </div>

            <div id="chatBody" class="chat-messages">
                <div class="chat-empty-state">
                    <i class="fas fa-comments"></i>
                    <p>Selecciona un contacto para comenzar a chatear</p>
                </div>
                <div class="typing-indicator" id="typingIndicator">
                    <i class="fas fa-circle-notch fa-spin"></i> Escribiendo...
                </div>
                <div class="new-messages-indicator" id="newMessagesIndicator">
                    <i class="fas fa-arrow-down"></i> Nuevos mensajes
                </div>
            </div>

            <!-- Barra de mensajes fija en la parte inferior -->
            <div class="message-input-container d-none" id="messageInputContainer">
                <div class="message-input-action" id="attachFileBtn" title="Adjuntar archivo">
                    <i class="fas fa-paperclip"></i>
                </div>
                <div class="message-input-action" id="attachImageBtn" title="Adjuntar imagen">
                    <i class="fas fa-image"></i>
                </div>
                <div class="message-input-action" id="likeButton" title="Enviar me gusta">
                    <i class="fas fa-heart"></i>
                </div>
                <textarea id="messageText" class="message-input" placeholder="Escribe un mensaje..." autocomplete="off" rows="1"></textarea>
                <div class="upload-progress" id="uploadProgress">
                    <div class="upload-progress-bar" id="uploadProgressBar"></div>
                </div>
                <button id="sendMessageBtn" class="send-button">
                    <i class="fas fa-paper-plane"></i>
                </button>
                <input type="file" id="fileInput" style="display: none;" />
                <input type="file" id="imageInput" accept="image/*" style="display: none;" />
            </div>
        </div>
    </div>

    <div id="lightboxModal" class="lightbox-modal">
        <span class="lightbox-close">&times;</span>
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; position: relative;">
            <img class="lightbox-content" id="lightboxImage" src="" alt="">
            <video class="lightbox-content video" id="lightboxVideo" src="" controls style="display: none;"></video>
            <a id="downloadBtn" href="#" download class="btn btn-primary" style="position: absolute; bottom: 20px; right: 20px; display: none;">
                <i class="fas fa-download"></i> Descargar
            </a>
        </div>
    </div>

    <script src="<?= APP_URL ?>/public/plugins/jquery/jquery.min.js"></script>
    <script src="<?= APP_URL ?>/public/dist/js/adminlte.min.js"></script>
    <script src="<?= APP_URL ?>/public/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script>
        let currentContactId = null;
        let messageCheckInterval;
        let lastMessageId = 0;
        let isPollingActive = false;
        let searchTimeout;
        
        const CURRENT_USER_ID = '<?= (int)$current_user_id ?>';
        const MAX_FILE_SIZE_MB = 16;
        const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;
        const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'tiff', 'svg'];
        const VIDEO_EXTENSIONS = ['mp4', 'webm', 'ogg']; 
        
        // Función para escapar HTML y prevenir XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Sistema de notificaciones toast
        function showNotification(message, type = 'info') {
            const notification = $(`
                <div class="notification toast-${type}">
                    <div class="notification-content">
                        <i class="fas fa-bell"></i>
                        <span>${message}</span>
                    </div>
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(() => notification.css('transform', 'translateX(0)'), 100);
            
            setTimeout(() => {
                notification.css('transform', 'translateX(100%)');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Función para verificar si estamos en la parte inferior del chat
        function isAtBottom(container, threshold = 100) {
            const element = container[0];
            return element.scrollHeight - element.scrollTop - element.clientHeight < threshold;
        }

        // Scroll suave hacia abajo
        function smoothScrollToBottom(container) {
            container.animate({
                scrollTop: container[0].scrollHeight
            }, 300);
        }

        $(document).ready(function () {
            loadContacts();

            function loadContacts() {
                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=get_contacts',
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            renderContacts(response.contacts);
                            fetchUnreadCounts();
                        } else {
                            $('#contactsContainer').html('<div class="p-3 text-center text-danger">Error al cargar contactos</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#contactsContainer').html('<div class="p-3 text-center text-danger">Error de conexión</div>');
                    }
                });
            }

            function fetchUnreadCounts() {
                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=get_unread_count',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Actualizar badges de no leídos por contacto
                            $('.contact-item').each(function() {
                                const contactId = $(this).data('contact-id');
                                const unreadCount = response.unread_by_contact[contactId] || 0;
                                let badge = $(this).find('.unread-badge');
                                if (unreadCount > 0) {
                                    if (badge.length === 0) {
                                        badge = $('<span class="unread-badge"></span>');
                                        $(this).append(badge);
                                    }
                                    badge.text(unreadCount);
                                } else {
                                    if (badge.length > 0) {
                                        badge.remove();
                                    }
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener conteos de no leídos:', error);
                    }
                });
            }

            function renderContacts(contacts) {
                const contactsContainer = $('#contactsContainer');
                contactsContainer.empty();
                if (contacts.length === 0) {
                    contactsContainer.html('<div class="p-3 text-center text-muted">No hay contactos disponibles</div>');
                    return;
                }
                contacts.forEach(contact => {
                    const displayName = contact.nombres && contact.apellidos ? `${contact.nombres} ${contact.apellidos}` : contact.email;
                    const contactHtml = `<div class="contact-item" data-contact-id="${contact.id_usuario}">
                                            <div class="contact-avatar">${displayName.charAt(0).toUpperCase()}</div>
                                            <div class="contact-info">
                                                <div class="contact-name">${escapeHtml(displayName)}</div>
                                                <div class="contact-email">${escapeHtml(contact.email)}</div>
                                            </div>
                                        </div>`;
                    contactsContainer.append(contactHtml);
                });
                $('.contact-item').off('click').on('click', function() {
                    $('.contact-item').removeClass('active');
                    $(this).addClass('active');
                    const contactId = $(this).data('contact-id');
                    selectContact(contactId);
                });
                
                // Configurar la búsqueda de contactos con debounce
                $('#searchContact').off('input').on('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const searchTerm = $(this).val().toLowerCase();
                        $('.contact-item').each(function() {
                            const contactName = $(this).find('.contact-name').text().toLowerCase();
                            const contactEmail = $(this).find('.contact-email').text().toLowerCase();
                            if (contactName.includes(searchTerm) || contactEmail.includes(searchTerm)) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    }, 300);
                });
            }

            function startMessagePolling(contactId) {
                if (messageCheckInterval) {
                    clearInterval(messageCheckInterval);
                }
                
                isPollingActive = true;
                messageCheckInterval = setInterval(async () => {
                    await checkNewMessages(contactId);
                }, 2000);
            }

            async function checkNewMessages(contactId) {
                try {
                    const response = await $.ajax({
                        url: `<?= APP_URL ?>/app/controllers/chat.php?action=check_new_messages&contact_id=${contactId}&last_id=${lastMessageId}`,
                        type: 'GET',
                        dataType: 'json'
                    });

                    if (response.success && response.new_messages.length > 0) {
                        // Solo agregar mensajes que no existan ya en el DOM
                        const newMessagesToAdd = response.new_messages.filter(message => 
                            !document.querySelector(`[data-message-id="${message.id_mensaje}"]`)
                        );
                        
                        if (newMessagesToAdd.length > 0) {
                            appendNewMessages(newMessagesToAdd);
                            lastMessageId = response.last_message_id;
                            
                            // Actualizar badges silenciosamente
                            updateUnreadBadges();
                            
                            // Mostrar notificación si no está en el chat
                            if (!isAtBottom($('#chatBody'))) {
                                $('#newMessagesIndicator').show();
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error checking new messages:', error);
                }
            }

            function appendNewMessages(messages) {
                const messagesContainer = $('#chatBody');
                const isScrolledToBottom = isAtBottom(messagesContainer);
                
                // Remover el estado vacío si existe
                $('.chat-empty-state').remove();
                
                messages.forEach(message => {
                    const messageElement = createMessageElement(message);
                    messagesContainer.append(messageElement);
                    
                    // Animación de entrada suave
                    messageElement.css({
                        opacity: 0,
                        transform: 'translateY(10px)'
                    }).animate({
                        opacity: 1,
                        transform: 'translateY(0)'
                    }, 300);
                });
                
                if (isScrolledToBottom) {
                    smoothScrollToBottom(messagesContainer);
                }
                
                // Re-configurar eventos después de agregar nuevos mensajes
                setupMessageEvents();
            }

            function createMessageElement(message) {
                const isSent = message.id_remitente == CURRENT_USER_ID;
                const messageTime = new Date(message.fecha_envio).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                let statusIcon = '';
                if (isSent) {
                    if (message.leido && parseInt(message.leido) === 1) { 
                        statusIcon = '<i class="fas fa-check-double message-status read-double"></i>';
                    } else {
                        statusIcon = '<i class="fas fa-check message-status sent-single"></i>';
                    }
                }
                
                let editedTag = (message.editado && message.editado === '1') ? '<span class="edited-tag">(editado)</span>' : '';
                
                // Verificar si el mensaje tiene "me gusta"
                const isLiked = message.liked && parseInt(message.liked) === 1;
                const likeCount = message.like_count || 0;
                const likeClass = isLiked ? 'liked-message' : '';
                const likeIcon = isLiked ? '❤️' : '';

                let messageContent = '';
                if (message.archivo && message.archivo.trim() !== '') {
                    const fileExtension = message.archivo.split('.').pop().toLowerCase();
                    const filePath = `<?= APP_URL ?>/${message.archivo}`;
                    const fileName = message.archivo.substring(message.archivo.lastIndexOf('/') + 1);

                    if (IMAGE_EXTENSIONS.includes(fileExtension)) {
                        messageContent += `<div class="message-image" data-file-url="${filePath}" data-file-type="image">
                                                <img src="${filePath}" alt="Imagen enviada" style="max-width: 250px;">
                                            </div>`;
                    } else if (VIDEO_EXTENSIONS.includes(fileExtension)) {
                        messageContent += `<div class="message-video" data-file-url="${filePath}" data-file-type="video">
                                                <video src="${filePath}" controls preload="metadata" style="max-width: 250px; border-radius: 8px;"></video>
                                            </div>`;
                    } else {
                        messageContent += `<a href="${filePath}" target="_blank" class="file-link">
                                                <i class="fas fa-file-download"></i> ${escapeHtml(fileName)}
                                            </a>`;
                    }
                }
                
                if (message.mensaje && message.mensaje.trim() !== '') {
                     if (messageContent !== '') {
                            messageContent += `<div class="message-text" style="margin-top: 10px;">${escapeHtml(message.mensaje)}</div>`;
                     } else {
                         messageContent += `<div class="message-text">${escapeHtml(message.mensaje)}</div>`;
                     }
                }
                
                // Si es un mensaje de "me gusta" sin texto
                if (message.tipo && message.tipo === 'like') {
                    messageContent = `<div class="sticker-message">❤️</div>`;
                }
                
                return $(`
                    <div class="message ${isSent ? 'sent' : 'received'} message-fade-in ${likeClass}" data-message-id="${message.id_mensaje}" data-liked="${isLiked ? '1' : '0'}" data-like-count="${likeCount}">
                        ${!isSent ? `<div class="message-avatar">${message.nombres ? message.nombres.charAt(0).toUpperCase() : 'U'}</div>` : ''}
                        <div class="message-content">
                            ${messageContent}
                            <div class="message-time">
                                ${messageTime}
                                ${editedTag}
                                ${statusIcon}
                                ${likeCount > 0 ? `<span class="like-count">${likeIcon} ${likeCount}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `);
            }

            function updateUnreadBadges() {
                // Actualizar badges sin recargar toda la página
                fetchUnreadCounts();
            }

            function selectContact(contactId) {
                currentContactId = contactId;
                $('#messageInputContainer').removeClass('d-none');
                $('#newMessagesIndicator').hide();
                
                const contactElement = $(`.contact-item[data-contact-id="${contactId}"]`);
                const contactName = contactElement.find('.contact-name').text();
                
                // Actualizar el encabezado del chat con indicadores de estado
                $('#chatHeader').html(`
                    <div class="chat-header-avatar">${contactName.charAt(0).toUpperCase()}</div>
                    <div class="chat-header-info">
                        <div class="chat-header-name">${escapeHtml(contactName)}</div>
                        <div class="contact-status">
                            <div class="status-dot"></div>
                            <span class="chat-header-status">En línea</span>
                        </div>
                    </div>
                    <div class="loading-indicator" id="headerLoading">
                        <i class="fas fa-circle-notch fa-spin"></i>
                    </div>
                    <div class="chat-header-actions">
                        <div class="chat-header-action">
                            <i class="fas fa-info-circle"></i>
                        </div>
                    </div>
                `);

                // Iniciar polling silencioso
                startMessagePolling(contactId);
                
                // Cargar mensajes iniciales
                loadMessages(contactId);
                
                // Quitar el badge de no leídos de inmediato al seleccionar el contacto
                const unreadBadge = contactElement.find('.unread-badge');
                if (unreadBadge.length > 0) {
                    unreadBadge.remove();
                }
                
                // Marcar todos como leídos
                markAllAsRead(contactId);
            }

            function markAllAsRead(contactId) {
                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=mark_as_read',
                    type: 'POST',
                    data: { contact_id: contactId },
                    dataType: 'json'
                });
            }

            function loadMessages(contactId) {
                const messagesContainer = $('#chatBody');
                
                // Mostrar indicador de carga
                $('#headerLoading').addClass('active');

                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=get_messages&contact_id=' + contactId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#headerLoading').removeClass('active');
                        
                        if (response.success) {
                            messagesContainer.empty();
                            
                            if (response.messages.length === 0) {
                                messagesContainer.html('<div class="chat-empty-state"><i class="fas fa-comment-slash"></i><p>No hay mensajes aún</p></div>');
                                lastMessageId = 0;
                                return;
                            }
                            
                            // Agregar mensajes con animación
                            response.messages.forEach(message => {
                                const messageElement = createMessageElement(message);
                                messagesContainer.append(messageElement);
                                lastMessageId = Math.max(lastMessageId, message.id_mensaje);
                            });
                            
                            // Scroll al final
                            smoothScrollToBottom(messagesContainer);
                            
                            // Configurar eventos después de renderizar
                            setupMessageEvents();
                        } else {
                            console.error('Error al cargar mensajes:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#headerLoading').removeClass('active');
                        console.error('Error en la solicitud:', error);
                    }
                });
            }

            function setupMessageEvents() {
                // Eventos de click para el lightbox
                $('.message-image, .message-video').off('click').on('click', function(e) {
                    e.preventDefault();
                    if ($(e.target).is('video')) {
                        return;
                    }
                    const fileUrl = $(this).data('file-url');
                    const fileType = $(this).data('file-type');
                    
                    $('#lightboxImage').hide();
                    $('#lightboxVideo').hide();
                    $('#downloadBtn').hide();

                    if (fileType === 'image') {
                        $('#lightboxImage').attr('src', fileUrl).show();
                        $('#downloadBtn').attr('href', fileUrl).show();
                    } else if (fileType === 'video') {
                        $('#lightboxVideo').attr('src', fileUrl).show();
                        $('#lightboxVideo')[0].load(); 
                        $('#downloadBtn').attr('href', fileUrl).show();
                    }
                    $('#lightboxModal').css('display', 'flex'); 
                });

                // Context menu para mensajes propios
                $('.message.sent').off('contextmenu').on('contextmenu', function(e) {
                    e.preventDefault();
                    const messageId = $(this).data('message-id');
                    showContextMenu(e.pageX, e.pageY, messageId);
                });
                
                // Context menu para mensajes recibidos (solo para dar me gusta)
                $('.message.received').off('contextmenu').on('contextmenu', function(e) {
                    e.preventDefault();
                    const messageId = $(this).data('message-id');
                    showLikeContextMenu(e.pageX, e.pageY, messageId);
                });

                // Evento para el indicador de nuevos mensajes
                $('#newMessagesIndicator').off('click').on('click', function() {
                    smoothScrollToBottom($('#chatBody'));
                    $(this).hide();
                });
            }

            function showContextMenu(x, y, messageId) {
                $('.context-menu').remove();
                
                const contextMenu = $(`
                    <ul class="context-menu list-group" data-message-id="${messageId}" style="position: absolute; top: ${y}px; left: ${x}px; z-index: 1000; min-width: 150px;">
                        <li class="list-group-item list-group-item-action edit-message"><i class="fas fa-edit"></i> Editar</li>
                        <li class="list-group-item list-group-item-action delete-for-me"><i class="fas fa-trash-alt"></i> Eliminar para mí</li>
                        <li class="list-group-item list-group-item-action delete-for-all"><i class="fas fa-trash"></i> Eliminar para todos</li>
                    </ul>
                `);
                $('body').append(contextMenu);

                $(document).off('click').on('click', function(e) {
                    if (!$(e.target).closest('.context-menu').length) {
                        $('.context-menu').remove();
                        $(document).off('click');
                    }
                });

                $('.edit-message').off('click').on('click', function() {
                    $('.context-menu').remove();
                    const messageElement = $(`.message[data-message-id="${messageId}"]`);
                    const currentMessageText = messageElement.find('.message-text').text();

                    Swal.fire({
                        title: 'Editar mensaje',
                        input: 'textarea',
                        inputValue: currentMessageText,
                        inputAttributes: {
                            'aria-label': 'Escribe tu nuevo mensaje'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            editMessage(messageId, result.value);
                        }
                    });
                });

                $('.delete-for-me').off('click').on('click', function() {
                    $('.context-menu').remove();
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Este mensaje se eliminará solo para ti.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteMessage(messageId, false);
                        }
                    });
                });

                $('.delete-for-all').off('click').on('click', function() {
                    $('.context-menu').remove();
                    Swal.fire({
                        title: '¿Eliminar para todos?',
                        text: "Esta acción no se puede revertir. El mensaje se eliminará para ti y para el destinatario.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteMessage(messageId, true);
                        }
                    });
                });
            }
            
            function showLikeContextMenu(x, y, messageId) {
                $('.context-menu').remove();
                
                const messageElement = $(`.message[data-message-id="${messageId}"]`);
                const isLiked = messageElement.data('liked') === '1';
                
                const contextMenu = $(`
                    <ul class="context-menu list-group" data-message-id="${messageId}" style="position: absolute; top: ${y}px; left: ${x}px; z-index: 1000; min-width: 150px;">
                        <li class="list-group-item list-group-item-action ${isLiked ? 'unlike-message' : 'like-message'}">
                            <i class="fas fa-heart ${isLiked ? 'text-danger' : ''}"></i> 
                            ${isLiked ? 'Quitar me gusta' : 'Dar me gusta'}
                        </li>
                    </ul>
                `);
                $('body').append(contextMenu);

                $(document).off('click').on('click', function(e) {
                    if (!$(e.target).closest('.context-menu').length) {
                        $('.context-menu').remove();
                        $(document).off('click');
                    }
                });

                $('.like-message, .unlike-message').off('click').on('click', function() {
                    $('.context-menu').remove();
                    toggleLike(messageId, !isLiked);
                });
            }

            function toggleLike(messageId, like) {
                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=toggle_like',
                    type: 'POST',
                    data: {
                        message_id: messageId,
                        like: like ? 'true' : 'false'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const messageElement = $(`.message[data-message-id="${messageId}"]`);
                            const currentLikeCount = parseInt(messageElement.data('like-count')) || 0;
                            const newLikeCount = like ? currentLikeCount + 1 : currentLikeCount - 1;
                            
                            // Actualizar datos del mensaje
                            messageElement.data('liked', like ? '1' : '0');
                            messageElement.data('like-count', newLikeCount);
                            
                            // Actualizar UI
                            if (like) {
                                messageElement.addClass('liked-message');
                            } else {
                                messageElement.removeClass('liked-message');
                            }
                            
                            // Actualizar contador de likes
                            let likeCountElement = messageElement.find('.like-count');
                            if (newLikeCount > 0) {
                                if (likeCountElement.length === 0) {
                                    messageElement.find('.message-time').append(`<span class="like-count">❤️ ${newLikeCount}</span>`);
                                } else {
                                    likeCountElement.text(`❤️ ${newLikeCount}`);
                                }
                            } else {
                                likeCountElement.remove();
                            }
                            
                            showNotification(like ? 'Me gusta agregado' : 'Me gusta removido', 'success');
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('No se pudo procesar la acción.', 'error');
                    }
                });
            }

            function deleteMessage(messageId, deleteForAll) {
                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=delete_message',
                    type: 'POST',
                    data: {
                        message_id: messageId,
                        delete_for_all: deleteForAll ? 'true' : 'false'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Mensaje eliminado correctamente', 'success');
                            // Eliminar solo el mensaje del DOM en lugar de recargar todo
                            $(`.message[data-message-id="${messageId}"]`).remove();
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('No se pudo eliminar el mensaje.', 'error');
                    }
                });
            }

            function editMessage(messageId, newMessage) {
                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=edit_message',
                    type: 'POST',
                    data: {
                        message_id: messageId,
                        new_message: newMessage
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Mensaje editado correctamente', 'success');
                            // Actualizar solo el mensaje editado en el DOM
                            const messageElement = $(`.message[data-message-id="${messageId}"]`);
                            messageElement.find('.message-text').html(escapeHtml(newMessage));
                            messageElement.find('.message-time').append('<span class="edited-tag">(editado)</span>');
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('No se pudo editar el mensaje.', 'error');
                    }
                });
            }
            
            // Evento de click para adjuntar archivo
            $('#attachFileBtn').click(function() {
                $('#fileInput').click();
            });
            
            // Evento de click para adjuntar imagen
            $('#attachImageBtn').click(function() {
                $('#imageInput').click();
            });
            
            // Evento de click para enviar "me gusta"
            $('#likeButton').click(function() {
                if (!currentContactId) {
                    showNotification('Selecciona un contacto primero', 'error');
                    return;
                }
                
                // Enviar mensaje de "me gusta"
                sendLike();
            });

            // Auto-resize textarea
            $('#messageText').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            $('#sendMessageBtn').click(sendMessage);
            $('#messageText').keypress(function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    if ($('#messageText').val().trim() !== '' || $('#fileInput')[0].files.length > 0 || $('#imageInput')[0].files.length > 0) {
                        sendMessage();
                    }
                }
            });

            function sendMessage() {
                const messageText = $('#messageText').val().trim();
                const fileInput = $('#fileInput')[0];
                const imageInput = $('#imageInput')[0];
                const file = fileInput.files[0] || imageInput.files[0];
                
                if (!messageText && !file || !currentContactId) {
                    return;
                }

                if (file && file.size > MAX_FILE_SIZE_BYTES) {
                    showNotification(`El archivo es demasiado grande. El tamaño máximo es de ${MAX_FILE_SIZE_MB}MB.`, 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('destinatario_id', currentContactId);
                formData.append('mensaje', messageText);
                if (file) {
                    formData.append('file', file);
                }

                // Mostrar progreso de subida
                $('#uploadProgress').show();
                
                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=send_message',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = (evt.loaded / evt.total) * 100;
                                $('#uploadProgressBar').css('width', percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $('#uploadProgress').hide();
                        $('#uploadProgressBar').css('width', '0%');
                        
                        if (response.success) {
                            $('#messageText').val('');
                            $('#messageText').css('height', 'auto');
                            $('#fileInput').val('');
                            $('#imageInput').val('');
                            
                            // En lugar de recargar todos los mensajes, agregar solo el nuevo mensaje
                            if (response.message_id) {
                                // Simular el mensaje enviado para mostrarlo inmediatamente
                                const tempMessage = {
                                    id_mensaje: response.message_id,
                                    id_remitente: CURRENT_USER_ID,
                                    mensaje: messageText,
                                    archivo: response.archivo || '',
                                    fecha_envio: new Date().toISOString(),
                                    leido: '0',
                                    nombres: '',
                                    apellidos: ''
                                };
                                
                                appendNewMessages([tempMessage]);
                                lastMessageId = Math.max(lastMessageId, response.message_id);
                            }
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#uploadProgress').hide();
                        $('#uploadProgressBar').css('width', '0%');
                        showNotification('Error al enviar el mensaje.', 'error');
                        console.error('Error al enviar mensaje:', error);
                    }
                });
            }
            
            function sendLike() {
                if (!currentContactId) {
                    return;
                }

                $.ajax({
                    url: '<?= APP_URL ?>/app/controllers/chat.php?action=send_like',
                    type: 'POST',
                    data: {
                        destinatario_id: currentContactId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Me gusta enviado', 'success');
                            
                            // Agregar el mensaje de "me gusta" al chat
                            if (response.message_id) {
                                const tempMessage = {
                                    id_mensaje: response.message_id,
                                    id_remitente: CURRENT_USER_ID,
                                    tipo: 'like',
                                    fecha_envio: new Date().toISOString(),
                                    leido: '0',
                                    nombres: '',
                                    apellidos: ''
                                };
                                
                                appendNewMessages([tempMessage]);
                                lastMessageId = Math.max(lastMessageId, response.message_id);
                            }
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error al enviar me gusta.', 'error');
                    }
                });
            }

            // Lightbox functionality
            $('.lightbox-close').click(function() {
                $('#lightboxModal').hide();
                $('#lightboxVideo')[0].pause();
            });

            $(document).click(function(e) {
                if ($(e.target).is('#lightboxModal')) {
                    $('#lightboxModal').hide();
                    $('#lightboxVideo')[0].pause();
                }
            });

            // Detener polling cuando se cierra la página
            $(window).on('beforeunload', function() {
                if (messageCheckInterval) {
                    clearInterval(messageCheckInterval);
                }
            });
        });
    </script>
</body>
</html>
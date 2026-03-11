<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: sign_in.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant · Unity Volunteers</title>
    <link rel="stylesheet" href="../assets/css/chatbot.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <i class="ri-robot-2-line"></i>
            <div>
                <h2>AI Assistant</h2>
                <p>Powered by Unity Volunteers Trust</p>
            </div>
            <span class="online-badge">ONLINE</span>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message bot-message">
                <div class="message-bubble">
                    👋 Hello! I'm your AI assistant. I can help you find events, get recommendations using machine learning!
                </div>
                <div class="message-time">Just now</div>
            </div>
        </div>
        
        <div class="chat-input-container">
            <div class="input-wrapper">
                <input type="text" id="messageInput" placeholder="Type your message..." onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()">
                    <i class="ri-send-plane-line"></i>
                </button>
            </div>
            
            <div class="quick-actions" id="quickActions">
                <button class="quick-action" onclick="quickAsk('recommend events for me')">
                    🎯 Recommend
                </button>
                <button class="quick-action" onclick="quickAsk('find events this weekend')">
                    📅 This weekend
                </button>
                <button class="quick-action" onclick="quickAsk('events needing First Aid')">
                    🏥 First Aid
                </button>
                <button class="quick-action" onclick="quickAsk('help')">
                    ❓ Help
                </button>
            </div>
        </div>
    </div>
    
    <script>
        let isTyping = false;
        const userId = <?php echo $_SESSION['userId']; ?>;
        
        function addMessage(text, isUser = false, events = null) {
            const messagesDiv = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
            
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.innerHTML = text.replace(/\n/g, '<br>');
            
            messageDiv.appendChild(bubble);
            
            const time = document.createElement('div');
            time.className = 'message-time';
            time.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            messageDiv.appendChild(time);
            
            messagesDiv.appendChild(messageDiv);
            
            
            if (events && events.length > 0) {
                events.slice(0, 3).forEach(event => {
                    const eventCard = document.createElement('div');
                    eventCard.className = 'event-card-mini';
                    eventCard.onclick = () => viewEvent(event.eventId);
                    eventCard.innerHTML = `
                        <div class="event-title">${event.eventName}</div>
                        <div class="event-details">
                            <span><i class="ri-calendar-line"></i> ${new Date(event.startDate).toLocaleDateString()}</span>
                            <span><i class="ri-map-pin-line"></i> ${event.location}</span>
                            ${event.skillName ? `<span class="skill-badge">${event.skillName}</span>` : ''}
                        </div>
                    `;
                    messagesDiv.appendChild(eventCard);
                });
            }
            
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        function showTyping() {
            if (isTyping) return;
            
            isTyping = true;
            const messagesDiv = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot-message';
            typingDiv.id = 'typingIndicator';
            
            const indicator = document.createElement('div');
            indicator.className = 'typing-indicator';
            indicator.innerHTML = `
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            `;
            
            typingDiv.appendChild(indicator);
            messagesDiv.appendChild(typingDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        function hideTyping() {
            isTyping = false;
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
        
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message === '') return;
            
            // Add user message
            addMessage(message, true);
            input.value = '';
            
            
            showTyping();
            
            // Send to chatbot
            fetch('../business_logic/chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    userId: userId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                hideTyping();
                addMessage(data.message, false, data.events);
                
                // Update quick actions 
                if (data.quick_actions) {
                    updateQuickActions(data.quick_actions);
                }
            })
            .catch(error => {
                hideTyping();
                addMessage("😔 Sorry, I'm having trouble connecting. Please try again.", false);
                console.error('Error:', error);
            });
        }
        
        function quickAsk(message) {
            document.getElementById('messageInput').value = message;
            sendMessage();
        }
        
        function handleKeyPress(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }
        
        function viewEvent(eventId) {
            window.location.href = `view_event.php?id=${eventId}`;
        }
        
        function updateQuickActions(actions) {
            const container = document.getElementById('quickActions');
            container.innerHTML = '';
            actions.forEach(action => {
                const btn = document.createElement('button');
                btn.className = 'quick-action';
                btn.textContent = action.text;
                btn.onclick = () => quickAsk(action.action);
                container.appendChild(btn);
            });
        }
    </script>
</body>
</html>
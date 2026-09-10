@php
    $hotlineRaw = $system['contact_hotline'] ?? $system['contact_phone'] ?? '0987622266';
    $hotlines = explode('|', $hotlineRaw);
    $primaryHotline = trim($hotlines[0] ?? '0987622266');
    $phoneHref = 'tel:' . preg_replace('/[^0-9+]/', '', $primaryHotline);
    
    $emailVal = trim($system['contact_email'] ?? 'truccomvn66@gmail.com');
    $emailHref = 'mailto:' . $emailVal;
    
    $messengerVal = trim($system['social_messenger'] ?? $system['social_facebook'] ?? '');
    if (!empty($messengerVal) && strpos($messengerVal, 'http') === 0) {
        $messengerHref = $messengerVal;
    } elseif (!empty($messengerVal)) {
        $messengerHref = 'https://m.me/' . $messengerVal;
    } else {
        $messengerHref = '#';
    }
    
    $zaloVal = trim($system['social_zalo'] ?? '');
    if (!empty($zaloVal) && strpos($zaloVal, 'http') === 0) {
        $zaloHref = $zaloVal;
    } elseif (!empty($zaloVal)) {
        $zaloHref = 'https://zalo.me/' . preg_replace('/[^0-9]/', '', $zaloVal);
    } else {
        $zaloHref = 'https://zalo.me/' . preg_replace('/[^0-9]/', '', $primaryHotline);
    }
@endphp

<!-- FLOATING ONLINE SUPPORT WIDGET -->
<div class="truc-online-support">
    <!-- Phone Call Button -->
    <a href="{{ $phoneHref }}" class="support-btn btn-phone" title="Gọi ngay: {{ $primaryHotline }}">
        <div class="phone-pulse"></div>
        <div class="btn-icon-wrapper">
            <i class="fa fa-phone"></i>
        </div>
        <span class="btn-tooltip">{{ $primaryHotline }}</span>
    </a>

    <!-- Email Button -->
    <a href="{{ $emailHref }}" class="support-btn btn-email" title="Gửi Email: {{ $emailVal }}">
        <div class="btn-icon-wrapper">
            <i class="fa fa-envelope"></i>
        </div>
        <span class="btn-tooltip">Email hỗ trợ</span>
    </a>

    <!-- Messenger Button -->
    <a href="{{ $messengerHref }}" target="_blank" rel="noopener noreferrer" class="support-btn btn-messenger" title="Chat Messenger">
        <div class="btn-icon-wrapper">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                <path d="M12 2C6.477 2 2 6.145 2 11.258c0 2.91 1.455 5.518 3.734 7.234V22l3.355-1.843c.925.257 1.905.397 2.911.397 5.523 0 10-4.145 10-9.258C22 6.145 17.523 2 12 2zm1.003 12.433l-2.549-2.72-4.97 2.72 5.467-5.8 2.607 2.72 4.912-2.72-5.467 5.8z"/>
            </svg>
        </div>
        <span class="btn-tooltip">Messenger</span>
    </a>

    <!-- Zalo Button -->
    <a href="{{ $zaloHref }}" target="_blank" rel="noopener noreferrer" class="support-btn btn-zalo" title="Chat Zalo">
        <div class="btn-icon-wrapper">
            <span class="zalo-text">Zalo</span>
        </div>
        <span class="btn-tooltip">Zalo Chat</span>
    </a>
</div>

<style>
.truc-online-support {
    position: fixed;
    right: 20px;
    bottom: 80px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 14px;
    align-items: center;
}

.truc-online-support .support-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #ffffff;
    color: #e5a800;
    box-shadow: 0 4px 15px rgba(229, 168, 0, 0.35);
    border: 2px solid #ffc700;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-decoration: none !important;
}

.truc-online-support .support-btn .btn-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 20px;
}

.truc-online-support .support-btn .zalo-text {
    font-weight: 800;
    font-size: 13px;
    letter-spacing: -0.5px;
    color: #e5a800;
    font-family: Arial, sans-serif;
}

/* Yellow Theme Styling & Hover Effects */
.truc-online-support .support-btn:hover {
    background: #ffc700;
    color: #ffffff;
    transform: scale(1.12);
    box-shadow: 0 6px 20px rgba(255, 199, 0, 0.6);
}

.truc-online-support .support-btn:hover .zalo-text {
    color: #ffffff;
}

/* Phone Call Button - White background circle so it stands out against yellow backgrounds */
.truc-online-support .btn-phone {
    background: #ffffff;
    color: #e5a800;
    border: 2px solid #ffc700;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
}

.truc-online-support .btn-phone .phone-pulse {
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    border-radius: 50%;
    border: 2px solid #ffc700;
    animation: yellowPulse 1.8s infinite ease-out;
    pointer-events: none;
}

@keyframes yellowPulse {
    0% {
        transform: scale(1);
        opacity: 0.9;
    }
    100% {
        transform: scale(1.6);
        opacity: 0;
    }
}

/* Tooltip Popup */
.truc-online-support .support-btn .btn-tooltip {
    position: absolute;
    right: 58px;
    top: 50%;
    transform: translateY(-50%) translateX(10px);
    background: #222222;
    color: #ffc700;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    pointer-events: none;
}

.truc-online-support .support-btn .btn-tooltip::after {
    content: '';
    position: absolute;
    right: -5px;
    top: 50%;
    transform: translateY(-50%);
    border-width: 5px 0 5px 5px;
    border-style: solid;
    border-color: transparent transparent transparent #222222;
}

.truc-online-support .support-btn:hover .btn-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(-50%) translateX(0);
}

@media (max-width: 768px) {
    .truc-online-support {
        right: 12px;
        bottom: 70px;
        gap: 10px;
    }
    .truc-online-support .support-btn {
        width: 42px;
        height: 42px;
    }
    .truc-online-support .support-btn .btn-icon-wrapper {
        font-size: 18px;
    }
    .truc-online-support .support-btn .zalo-text {
        font-size: 11px;
    }
}
</style>

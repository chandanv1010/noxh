{{--
    Dau hieu cua tung mang xa hoi.

    KHONG nam trong bo icon chung: Material Symbols la bo hinh chuc nang, cac
    dau hieu thuong hieu khong thuoc bo do. Facebook va YouTube ve lai bang
    hinh don gian; Zalo von la chu nen de nguyen chu; TikTok dung not nhac -
    gan dung hinh trong ban thiet ke, khong phai dau hieu chinh thuc.

    Cach dung: @include('frontend.noxh.component.social-icon', ['ten' => 'facebook'])
--}}
@switch($ten)
    @case('facebook')
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M15.6 8.2h-2.1V6.9c0-.6.4-.8.7-.8h1.3V3.6l-1.9-.1c-2.2 0-3.3 1.4-3.3 3.2v1.5H8.4v2.6h1.9v7.6h3.2v-7.6h2l.1-2.6z"/>
        </svg>
        @break

    @case('youtube')
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M21.1 8.1a2.4 2.4 0 0 0-1.7-1.7C17.9 6 12 6 12 6s-5.9 0-7.4.4A2.4 2.4 0 0 0 2.9 8.1 25 25 0 0 0 2.5 12c0 1.3.1 2.6.4 3.9a2.4 2.4 0 0 0 1.7 1.7c1.5.4 7.4.4 7.4.4s5.9 0 7.4-.4a2.4 2.4 0 0 0 1.7-1.7c.3-1.3.4-2.6.4-3.9s-.1-2.6-.4-3.9zM10.1 14.8V9.2l4.9 2.8-4.9 2.8z"/>
        </svg>
        @break

    @case('zalo')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <text x="12" y="15.4" text-anchor="middle" fill="currentColor"
                  font-family="Inter, Segoe UI, sans-serif" font-size="9" font-weight="800">Zalo</text>
        </svg>
        @break

    @case('tiktok')
        <svg viewBox="0 -960 960 960" fill="currentColor" aria-hidden="true" focusable="false">
            <path d="M400-120q-66 0-113-47t-47-113q0-66 47-113t113-47q11 0 20.5 1t19.5 4v84q-9-3-19-4.5t-21-1.5q-32 0-56 24t-24 56q0 32 24 56t56 24q32 0 56-24t24-56v-543h80q0 26 10 49.5t27.5 41Q625-712 648-702t49 10v80q-43 0-81-14.5T546-670v390q0 66-47 113t-99 47Z"/>
        </svg>
        @break

    @default
        @include('frontend.noxh.component.icon', ['name' => 'globe', 'size' => 20])
@endswitch

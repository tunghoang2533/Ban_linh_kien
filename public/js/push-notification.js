/**
 * Push Notification Manager
 * Đăng ký Service Worker và xử lý push notification subscription
 */

(function() {
    'use strict';

    const BASE_URL = window.location.origin + '/Ban_linh_kien/';
    const SW_PATH = BASE_URL + 'public/js/sw.js';

    // Kiểm tra browser support
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.log('❌ Push notifications not supported');
        return;
    }

    let swRegistration = null;

    /**
     * Đăng ký Service Worker
     */
    async function registerSW() {
        try {
            swRegistration = await navigator.serviceWorker.register(SW_PATH);
            console.log('✅ Service Worker registered');
            return swRegistration;
        } catch (err) {
            console.error('❌ Service Worker registration failed:', err);
            return null;
        }
    }

    /**
     * Chuyển đổi VAPID public key sang Uint8Array
     */
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');
        const rawData = atob(base64);
        return Uint8Array.from([...rawData].map(ch => ch.charCodeAt(0)));
    }

    /**
     * Đăng ký nhận push notification
     */
    async function subscribeUser() {
        if (!swRegistration) {
            swRegistration = await registerSW();
            if (!swRegistration) return false;
        }

        try {
            const subscription = await swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    // VAPID public key — injected từ server trong header
                    window.VAPID_PUBLIC_KEY || ''
                )
            });

            // Gửi subscription lên server
            await fetch(BASE_URL + 'notification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'subscribe_push',
                    endpoint: subscription.endpoint,
                    auth_key: btoa(String.fromCharCode.apply(null, 
                        new Uint8Array(subscription.getKey('auth'))
                    )),
                    p256dh_key: btoa(String.fromCharCode.apply(null, 
                        new Uint8Array(subscription.getKey('p256dh'))
                    ))
                })
            });

            console.log('✅ Push subscription saved');
            return true;
        } catch (err) {
            console.error('❌ Push subscription failed:', err);
            return false;
        }
    }

    /**
     * Hủy đăng ký push notification
     */
    async function unsubscribeUser() {
        if (!swRegistration) return false;

        try {
            const subscription = await swRegistration.pushManager.getSubscription();
            if (subscription) {
                await subscription.unsubscribe();
                await fetch(BASE_URL + 'notification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'unsubscribe_push',
                        endpoint: subscription.endpoint
                    })
                });
                console.log('✅ Push unsubscribed');
            }
            return true;
        } catch (err) {
            console.error('❌ Push unsubscribe failed:', err);
            return false;
        }
    }

    /**
     * Kiểm tra trạng thái subscription
     */
    async function checkSubscription() {
        if (!swRegistration) return false;
        const subscription = await swRegistration.pushManager.getSubscription();
        return !!subscription;
    }

    // Export functions for global use
    window.PushManager = {
        register: registerSW,
        subscribe: subscribeUser,
        unsubscribe: unsubscribeUser,
        checkSubscription: checkSubscription
    };

    // Tự động đăng ký khi trang load (nếu user đã đăng nhập)
    if (window.USER_LOGGED_IN) {
        document.addEventListener('DOMContentLoaded', function() {
            registerSW().then(() => {
                // Kiểm tra subscription hiện tại
                checkSubscription().then(isSubscribed => {
                    if (!isSubscribed) {
                        // Hỏi người dùng trước khi đăng ký
                        setTimeout(() => {
                            if (confirm('🔔 Bật thông báo để nhận tin khuyến mãi và cập nhật đơn hàng?')) {
                                subscribeUser();
                            }
                        }, 3000);
                    }
                });
            });
        });
    }

})();

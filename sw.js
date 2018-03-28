'use strict';

const serverUrl = 'https://teko-wpn.gtk.vn/api';

self.addEventListener('push', function(event) {
    console.log('[Service Worker] Push Received.');
    console.log(`[Service Worker] Push had this data: "${event.data.text()}"`);

    event.waitUntil(
        self.registration.pushManager.getSubscription().then(function (subscription) {
            var notificationUrl = serverUrl + '/notifications/last?endpoint=' + encodeURI(subscription.endpoint);

            return fetch(notificationUrl).then(function (response) {
                if (response.status !== 200) {
                    console.log('Looks like there was a problem. Status Code: ' + response.status);
                    throw new Error();
                }

                return response.json();
            }).then(function (response) {
                if (!response.notification) {
                    console.error('The API returned an error: ', response.error.message);
                    throw new Error();
                }

                var title = response.notification.title;
                var body = response.notification.body;
                var icon = 'images/icon.png';
                var data = {
                    url: response.notification.url
                };

                return self.registration.showNotification(title, {
                    body: body,
                    icon: icon,
                    data: data
                });
            }).catch(function (error) {
                console.error('Unable to retrieve data while sending ', error);
            });
        })
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    var url = event.notification.data.url;

    event.waitUntil(
        clients.matchAll({
            type: 'window'
        }).then(function (clientsList) {
            for (var i = 0; i < clientsList.length; i++) {
                var client = clientsList[i];

                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
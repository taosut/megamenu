/* eslint-env browser, es6 */

'use strict';

const applicationServerPublicKey = 'BM1tABDJ1ZWv97yvOGkxzigqbGGcdMH1w9Eu16d7uWBiYZEplRwlTTQDWi-LeyyfQ23VsDH01oA6STFkfuhC16k';
const serverUrl = 'https://teko-wpn.gtk.vn/api';

let isSubscribed = false;
let swRegistration = null;

function urlB64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

function check_browser_version() {
    let nAgt = navigator.userAgent;
    let fullVersion = '' + parseFloat(navigator.appVersion);
    let majorVersion = parseInt(navigator.appVersion, 10);
    let verOffset;

    if ((verOffset = nAgt.indexOf('Chrome')) != -1) {
        fullVersion = nAgt.substring(verOffset + 7);
    } else if ((verOffset = nAgt.indexOf('Safari')) != -1) {
        fullVersion = nAgt.substring(verOffset + 7);
        if ((verOffset = nAgt.indexOf('Version')) != -1) {
            fullVersion = nAgt.substring(verOffset + 8);
        }
    } else if ((verOffset = nAgt.indexOf('Firefox')) != -1) {
        fullVersion = nAgt.substring(verOffset + 8);
    }

    majorVersion = parseInt('' + fullVersion, 10);

    return majorVersion;
}

function check_browser() {
    let name = navigator.userAgent;
    let browser = name.match(/(opera|chrome|safari|firefox|msie)\/?\s*/i);

    return browser[1];
}

function updateSubscriptionOnServer(subscription) {
    if (subscription == null) {
        return;
    }

    let payload;

    if (typeof USER_ID !== 'undefined') {
        payload = {
            identifier: USER_ID,
            subscription: subscription
        };
    } else {
        payload = {
            subscription: subscription
        };
    }

    // Send subscription to application server
    axios.post(serverUrl + '/subscriptions', payload).then(function (response) {
        // 
    }).catch(function (error) {
        console.log(error);
    });
}

function removeSubscriptionOnServer(subscription) {
    axios.post(serverUrl + '/subscriptions/delete', JSON.stringify(subscription)).then(function (response) {
        // 
    }).catch(function (error) {
        console.log(error);
    });

    return subscription.unsubscribe();
}

function subscribeUser() {
    const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);

    swRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    }).then(function (subscription) {
        console.log('User is subscribed.');

        updateSubscriptionOnServer(subscription);

        isSubscribed = true;
    }).catch(function (err) {
        console.log('Failed to subscribe the user: ', err);
    });
}

function unsubscribeUser() {
    swRegistration.pushManager.getSubscription().then(function (subscription) {
        if (subscription) {
            return removeSubscriptionOnServer(subscription);
        }
    }).catch(function (error) {
        console.log('Error unsubscribing', error);
    }).then(function () {
        updateSubscriptionOnServer(null);

        console.log('User is unsubscribed.');
        isSubscribed = false;
    });
}

function initialiseUI() {
    if (! isSubscribed) {
        subscribeUser();
    }

    // Set the initial subscription value
    swRegistration.pushManager.getSubscription().then(function (subscription) {
        isSubscribed = !(subscription === null);

        updateSubscriptionOnServer(subscription);

        if (isSubscribed) {
          console.log('User IS subscribed.');
        } else {
          console.log('User is NOT subscribed.');
        }
    });
}

function registerServiceWorker() {
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        console.log('Service Worker and Push is supported');

        navigator.serviceWorker.register('/sw.js').then(function (swReg) {
            console.log('Service Worker is registered', swReg);

            swRegistration = swReg;
            initialiseUI();
        }).catch(function (error) {
            console.error('Service Worker Error', error);
        });
    } else {
        console.warn('Push messaging is not supported');
    }
}

function registerSW() {
    if (('Firefox' === check_browser() && check_browser_version() > 43) || ('Chrome' === check_browser() && 42 <= check_browser_version())) {
        registerServiceWorker();
    } else if ('Safari' === check_browser() && check_browser_version() >= 7) {
        console.log('notify safari');
    }
}

// self.addEventListener('load', function() {
//     registerSW();
// });
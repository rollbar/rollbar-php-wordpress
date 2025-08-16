/**
 * Rollbar for WordPress admin JS.
 *
 * @var {{
 *    nonce: string,
 *    rest_root: string,
 *    plugin_url: string,
 * }} rollbarSettings Declared in SettingsPage::enqueueAdminScripts().
 */

// import Rollbar from 'rollbar';

document.addEventListener('DOMContentLoaded', () => {
    // Collapse Settings Sections Toggles
    const sectionHeaders = document.querySelectorAll('.rollbar-settings-section-header');
    sectionHeaders.forEach(header => {
        const toggle = header.querySelector('.section-heading');
        toggle.addEventListener('click', () => {
            header.classList.toggle('open');
        });
    });

    const enablePhpInput = document.getElementById('rollbar_wp_php_logging_enabled');
    const enableJsInput = document.getElementById('rollbar_wp_js_logging_enabled');

    // Toggle PHP/JS logging
    Object.entries({
        "rollbar_wp_server_side_access_token_container": enablePhpInput,
        "rollbar_wp_client_side_access_token_container": enableJsInput,
    }).forEach(([container, el]) => {
        // Include here so they start open.
        if (el.checked) {
            document.getElementById(container).classList.remove('hidden');
        }
        el.addEventListener('input', () => {
            if (el.checked) {
                document.getElementById(container).classList.remove('hidden');
                return;
            }
            document.getElementById(container).classList.add('hidden');
        });
    });

    // Reset Default
    document.querySelectorAll('.rollbar_wp_restore_default').forEach(el => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            const setting = el.getAttribute('data-setting');
            let defaultValue = el.getAttribute('data-default');
            const type = el.getAttribute('data-setting-input-type');
            const input = document.querySelectorAll(`[data-setting="${setting}"]`);
            if (null === input || input.length === 0) {
                return;
            }
            switch (type) {
                case 'boolean':
                    input[0].checked = defaultValue === 'true';
                    break;
                case 'checkbox':
                    defaultValue = defaultValue.split(',');
                    for (const checkbox of input) {
                        checkbox.checked = defaultValue.includes(checkbox.value);
                    }
                    break;
                default:
                    input[0].value = defaultValue;
            }
        })
    });

    const testMessageContainer = document.getElementById('rollbar_test_message_container');

    /**
     * Returns a formatted notice message HTML Node.
     *
     * @param {string} type    The notice type class.
     * @param {string} message The notice message.
     * @returns {ChildNode}
     */
    const makeNotice = (type, message) => {
        const el = document.createElement('div');
        el.innerHTML = `<div class="notice ${type} is-dismissible">${message}</div>`;
        return el.firstChild;
    };

    /**
     * Request a test of the Rollbar config in PHP via the REST API.
     */
    const testPhpLogging = () => {
        if (enablePhpInput.checked === false) {
            testMessageContainer.appendChild(makeNotice(
                'notice-warning',
                `<p><strong>PHP Test:</strong> Skipped testing logging since it is disabled.</p>`,
            ));
            return;
        }
        fetch(`${rollbarSettings.rest_root}rollbar/v1/test-php-logging`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': rollbarSettings.nonce,
            },
        }).then(response => {
            response.json().then(data => {
                if (data.success) {
                    testMessageContainer.appendChild(makeNotice(
                        'notice-success',
                        `<p><strong>PHP Test:</strong> Test message sent to Rollbar using PHP. Please check 
                                      your Rollbar dashboard to see if you received it. Save your changes and you're 
                                      ready to go.</p>`,
                    ));
                    return;
                }
                testMessageContainer.appendChild(makeNotice(
                    'notice-error',
                    `<p><strong>PHP Test:</strong> There was a problem accessing Rollbar service.</p>
                              <ul>
                                  <li>Code:<code>${data.code}</code></li>
                                  <li>Message:<code>${data.message}</code></li>
                              </ul>`,
                ));
            })
        }).finally(() => {
            testButton.disabled = false;
        });
    };

    /**
     * Send the test log message to the Rollbar service.
     *
     * @param {string} token The client side access token.
     * @param {string} env   The environment.
     */
    const sendTestJsMessage = (token, env) => {
        Rollbar.configure({
            accessToken: token,
            captureUncaught: true,
            captureUnhandledRejections: true,
            payload: {
                environment: env
            }
        });
        Rollbar.info(
            "Test message from Rollbar WordPress plugin using JS: integration with WordPress successful",
            function(error, data) {
                if (undefined !== data) {
                    testMessageContainer.appendChild(makeNotice(
                        'notice-success',
                        `<p><strong>JS Test:</strong> Test message sent to Rollbar using JS. Please check 
                                      your Rollbar dashboard to see if you received it. Save your changes and you're 
                                      ready to go.</p>`,
                    ));
                    return;
                }
                testMessageContainer.appendChild(makeNotice(
                    'notice-error',
                    `<p><strong>JS Test:</strong> There was a problem accessing Rollbar service using provided credentials for JS logging. Check your client side token.</p>`,
                ));
            }
        );
    };

    /**
     * Test the Rollbar JS configuration to ensure it is working.
     */
    const testJsLogging = () => {
        const clientSideAccessToken = document.getElementById('rollbar_wp_client_side_access_token').value;
        const environment = document.getElementById('rollbar_wp_environment').value;
        if (enableJsInput.checked === false) {
            testMessageContainer.appendChild(makeNotice(
                'notice-warning',
                `<p><strong>JS Test:</strong> Skipped testing logging since it is disabled.</p>`,
            ));
            return;
        }

        // Depending on the plugin status prior to loading the page, the Rollbar object may not exist. So we fetch it.
        if (window.Rollbar === undefined) {

            fetch(rollbarSettings.plugin_url + "public/js/rollbar.snippet.js")
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    eval(data);
                    sendTestJsMessage(clientSideAccessToken, environment);
                })
                .catch(() => {
                    jsFailNotice();
                });
        } else {
            // This is inside an "else" since the fetch is async.
            sendTestJsMessage(clientSideAccessToken, environment);
        }
    };

    // Send Test Message
    const testButton = document.getElementById('rollbar_wp_test_logging');
    testButton?.addEventListener('click', () => {
        testButton.disabled = true;
        testMessageContainer.innerHTML = '';
        testPhpLogging();
        testJsLogging();
    });
});
